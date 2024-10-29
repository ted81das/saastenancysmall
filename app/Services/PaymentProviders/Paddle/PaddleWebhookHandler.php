<?php

namespace App\Services\PaymentProviders\Paddle;

use App\Constants\OrderStatus;
use App\Constants\OrderStatusConstants;
use App\Constants\PaymentProviderConstants;
use App\Constants\SubscriptionStatus;
use App\Constants\TransactionStatus;
use App\Models\Currency;
use App\Models\Order;
use App\Models\PaymentProvider;
use App\Services\OrderManager;
use App\Services\SubscriptionManager;
use App\Services\TransactionManager;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaddleWebhookHandler
{
    public function __construct(
        private SubscriptionManager $subscriptionManager,
        private TransactionManager $transactionManager,
        private OrderManager $orderManager,
    ) {

    }

    public function handleWebhook(Request $request): JsonResponse
    {
        if (! $this->validateSignature($request)) {
            return response()->json([
                'message' => 'Invalid signature',
            ], 400);
        }

        $event = $request->all();

        $paymentProvider = PaymentProvider::where('slug', PaymentProviderConstants::PADDLE_SLUG)->firstOrFail();

        $eventType = $event['event_type'];
        $eventData = $event['data'];
        if ($eventType == 'subscription.created' ||
            $eventType == 'subscription.updated' ||
            $eventType == 'subscription.resumed' ||
            $eventType == 'subscription.paused' ||
            $eventType == 'subscription.canceled' ||
            $eventType == 'subscription.activated' ||
            $eventType == 'subscription.past_due' ||
            $eventType == 'subscription.trialing'
        ) {
            $subscriptionUuid = $eventData['custom_data']['subscriptionUuid'] ?? null;

            if (! $subscriptionUuid) {
                return response()->json([
                    'message' => 'Subscription uuid not found',
                ], 400);
            }

            $subscription = $this->subscriptionManager->findByUuidOrFail($subscriptionUuid);
            $paddleSubscriptionStatus = $eventData['status'];
            $subscriptionStatus = $this->mapPaddleSubscriptionStatusToSubscriptionStatus($paddleSubscriptionStatus);
            $endsAt = $eventData['current_billing_period']['ends_at'] ?? null;

            if ($endsAt == null) {
                $endsAt = Carbon::now()->toDateTimeString();
            } else {
                $endsAt = Carbon::parse($endsAt)->toDateTimeString();
            }

            $item = $eventData['items'][0] ?? null;

            if ($item === null) {
                return response()->json([
                    'message' => 'Subscription item not found',
                ], 400);
            }

            $trialDates = $item['trial_dates'] ?? null;
            $trialEndsAt = null;
            if ($trialDates) {
                $trialEndsAt = Carbon::parse($trialDates['ends_at'])->toDateTimeString();
            }

            $canceledAt = $eventData['canceled_at'] ?? null;
            if ($canceledAt) {
                $canceledAt = Carbon::parse($canceledAt)->toDateTimeString();
            }

            $this->subscriptionManager->updateSubscription($subscription, [
                'status' => $subscriptionStatus,
                'ends_at' => $endsAt,
                'payment_provider_subscription_id' => $eventData['id'],
                'payment_provider_status' => $paddleSubscriptionStatus,
                'payment_provider_id' => $paymentProvider->id,
                'trial_ends_at' => $trialEndsAt,
                'canceled_at' => $canceledAt,
                'quantity' => $item['quantity'] ?? 1,
            ]);
        } elseif ($eventType == 'transaction.created') {
            $subscriptionUuid = $eventData['custom_data']['subscriptionUuid'] ?? null;
            $orderUuid = $eventData['custom_data']['orderUuid'] ?? null;

            if (! empty($subscriptionUuid)) {
                $this->handleTransactionCreatedForSubscription($subscriptionUuid, $eventData, $paymentProvider);
            } elseif (! empty($orderUuid)) {
                $this->handleTransactionCreatedForOrder($orderUuid, $eventData, $paymentProvider);
            } else {
                return response()->json([
                    'message' => 'Subscription uuid or order uuid not found',
                ], 400);
            }

        } elseif (
            $eventType == 'transaction.billed' ||
            $eventType == 'transaction.canceled' ||
            $eventType == 'transaction.completed' ||
            $eventType == 'transaction.ready' ||
            $eventType == 'transaction.updated'
        ) {
            $paddleTransactionId = $eventData['id'] ?? null;
            $paddleTransactionStatus = $eventData['status'];
            // update transaction

            $total = $eventData['details']['totals']['grand_total'];  // proration leads to amount update (= 0)
            $fees = $eventData['details']['totals']['fee'] ?? 0;

            $this->transactionManager->updateTransactionByPaymentProviderTxId(
                $paddleTransactionId,
                $paddleTransactionStatus,
                $this->mapPaddleTransactionStatusToTransactionStatus($paddleTransactionStatus),
                null,
                intval($total),
                intval($fees),
            );

            $orderUuid = $eventData['custom_data']['orderUuid'] ?? null;
            if (! empty($orderUuid)) {
                $order = $this->orderManager->findByUuidOrFail($orderUuid);
                $currentStatus = OrderStatus::tryFrom($order->status);
                $newStatus = $this->mapPaddleTransactionStatusToOrderStatus($paddleTransactionStatus);

                if (! in_array($currentStatus, OrderStatusConstants::FINAL_STATUSES) ||
                    (in_array($currentStatus, OrderStatusConstants::FINAL_STATUSES) && in_array($newStatus, OrderStatusConstants::FINAL_STATUSES))) {
                    // we only update the order status if it's not in a final state or if it's in a final state and the new status is also a final state
                    // this is to prevent updating the order status from a final state to a non-final state due to a webhook event coming in late
                    $this->orderManager->updateOrder($order, [
                        'status' => $this->mapPaddleTransactionStatusToOrderStatus($paddleTransactionStatus),
                        'payment_provider_id' => $paymentProvider->id,
                    ]);
                }
            }
        } elseif (
            $eventType == 'transaction.past_due' ||
            $eventType == 'transaction.payment_failed'
        ) {
            $paddleTransactionId = $eventData['id'] ?? null;
            $paddleTransactionStatus = $eventData['status'];
            // update transaction

            $errorReason = $eventData['payments'] ? ($eventData['payments'][0]['error_code'] ?? null) : null;

            $this->transactionManager->updateTransactionByPaymentProviderTxId(
                $paddleTransactionId,
                $paddleTransactionStatus,
                $this->mapPaddleTransactionStatusToTransactionStatus($paddleTransactionStatus),
                $errorReason,
            );

            $subscriptionUuid = $eventData['custom_data']['subscriptionUuid'] ?? null;
            $orderUuid = $eventData['custom_data']['orderUuid'] ?? null;

            if (! empty($subscriptionUuid)) {
                $this->handleTransactionFailedOrPastDueForSubscription($subscriptionUuid);
            } elseif (! empty($orderUuid)) {
                $this->handleTransactionFailedOrPastDueForOrder($orderUuid);
            } else {
                return response()->json([
                    'message' => 'Subscription uuid or order uuid not found',
                ], 400);
            }
        } elseif (
            $eventType == 'adjustment.created' ||
            $eventType == 'adjustment.updated'
        ) {
            $paddleTransactionId = $eventData['transaction_id'] ?? null;
            $action = $eventData['action'];
            $paddleTransactionStatus = $eventData['status'];
            // update transaction

            if ($action == 'refund' && $paddleTransactionStatus == 'approved') {
                $this->transactionManager->updateTransactionByPaymentProviderTxId(
                    $paddleTransactionId,
                    'refunded',
                    TransactionStatus::REFUNDED,
                );

                $transaction = $this->transactionManager->getTransactionByPaymentProviderTxId($paddleTransactionId);
                $order = $transaction->order;

                if ($order) {
                    $this->orderManager->updateOrder($order, [
                        'status' => OrderStatus::REFUNDED,
                    ]);
                }
            } elseif ($action == 'chargeback') {
                $this->transactionManager->updateTransactionByPaymentProviderTxId(
                    $paddleTransactionId,
                    'chargeback',
                    TransactionStatus::DISPUTED,
                );

                $transaction = $this->transactionManager->getTransactionByPaymentProviderTxId($paddleTransactionId);
                $order = $transaction->order;

                if ($order) {
                    $this->orderManager->updateOrder($order, [
                        'status' => OrderStatus::DISPUTED,
                    ]);
                }
            }
        } else {
            return response()->json();
        }

        return response()->json();
    }

    private function handleTransactionCreatedForSubscription(string $subscriptionUuid, array $eventData, PaymentProvider $paymentProvider): void
    {
        $paddleTransactionId = $eventData['id'] ?? null;

        $subscription = $this->subscriptionManager->findByUuidOrFail($subscriptionUuid);
        $currency = Currency::where('code', strtoupper($eventData['currency_code']))->firstOrFail();
        $paddleTransactionStatus = $eventData['status'];
        $total = $eventData['details']['totals']['grand_total'];
        $fees = $eventData['details']['totals']['fee'] ?? 0;
        $discounts = $eventData['details']['totals']['discount'] ?? 0;
        $taxes = $eventData['details']['totals']['tax'] ?? 0;
        // create transaction

        $this->transactionManager->createForSubscription(
            $subscription,
            intval($total),
            intval($taxes),
            intval($discounts),
            intval($fees),
            $currency,
            $paymentProvider,
            $paddleTransactionId,
            $paddleTransactionStatus,
            $this->mapPaddleTransactionStatusToTransactionStatus($paddleTransactionStatus),
        );
    }

    private function handleTransactionCreatedForOrder(string $orderUuid, array $eventData, PaymentProvider $paymentProvider): void
    {
        $paddleTransactionId = $eventData['id'] ?? null;

        $order = $this->orderManager->findByUuidOrFail($orderUuid);
        $currency = Currency::where('code', strtoupper($eventData['currency_code']))->firstOrFail();
        $paddleTransactionStatus = $eventData['status'];
        $total = $eventData['details']['totals']['grand_total'];
        $fees = $eventData['details']['totals']['fee'] ?? 0;
        $discounts = $eventData['details']['totals']['discount'] ?? 0;
        $taxes = $eventData['details']['totals']['tax'] ?? 0;
        // create transaction

        $this->transactionManager->createForOrder(
            $order,
            intval($total),
            intval($taxes),
            intval($discounts),
            intval($fees),
            $currency,
            $paymentProvider,
            $paddleTransactionId,
            $paddleTransactionStatus,
            $this->mapPaddleTransactionStatusToTransactionStatus($paddleTransactionStatus),
        );
    }

    private function handleTransactionFailedOrPastDueForSubscription(string $subscriptionUuid): void
    {
        $subscription = $this->subscriptionManager->findByUuidOrFail($subscriptionUuid);
        $this->subscriptionManager->handleInvoicePaymentFailed($subscription);
    }

    private function handleTransactionFailedOrPastDueForOrder(string $oderUuid): void
    {
        $order = $this->orderManager->findByUuidOrFail($oderUuid);
        $this->orderManager->updateOrder($order, [
            'status' => OrderStatus::FAILED,
        ]);
    }

    private function mapPaddleTransactionStatusToTransactionStatus(string $paddleStatus): TransactionStatus
    {
        if ($paddleStatus == 'completed') {
            return TransactionStatus::SUCCESS;
        }

        if ($paddleStatus == 'canceled') {
            return TransactionStatus::FAILED;
        }

        if ($paddleStatus == 'ready' ||
            $paddleStatus == 'billed' ||
            $paddleStatus == 'past_due' ||
            $paddleStatus == 'paid'
        ) {
            return TransactionStatus::PENDING;
        }

        if ($paddleStatus == 'draft') {
            return TransactionStatus::NOT_STARTED;
        }

        return TransactionStatus::NOT_STARTED;
    }

    private function mapPaddleTransactionStatusToOrderStatus(string $paddleStatus): OrderStatus
    {
        if ($paddleStatus == 'completed') {
            return OrderStatus::SUCCESS;
        }

        if ($paddleStatus == 'canceled') {
            return OrderStatus::FAILED;
        }

        if ($paddleStatus == 'ready' ||
            $paddleStatus == 'billed' ||
            $paddleStatus == 'past_due' ||
            $paddleStatus == 'paid'
        ) {
            return OrderStatus::PENDING;
        }

        return OrderStatus::NEW;
    }

    private function mapPaddleSubscriptionStatusToSubscriptionStatus(string $paddleSubscriptionStatus)
    {
        if ($paddleSubscriptionStatus == 'active' || $paddleSubscriptionStatus == 'trialing') {
            return SubscriptionStatus::ACTIVE;
        }

        if ($paddleSubscriptionStatus == 'past_due') {
            return SubscriptionStatus::PAST_DUE;
        }

        if ($paddleSubscriptionStatus == 'canceled') {
            return SubscriptionStatus::CANCELED;

        }

        if ($paddleSubscriptionStatus == 'paused') {
            return SubscriptionStatus::PAUSED;
        }

        return SubscriptionStatus::INACTIVE;

    }

    private function validateSignature(Request $request): bool
    {
        $secret = config('services.paddle.webhook_secret');
        $signature = $request->header('Paddle-Signature', '');

        $timestampPart = explode(';', $signature)[0] ?? '';
        $h1Part = explode(';', $signature)[1] ?? '';
        $timestamp = explode('=', $timestampPart)[1] ?? '';
        $h1 = explode('=', $h1Part)[1] ?? '';

        $data = $timestamp.':'.$request->getContent();

        $contentSignature = hash_hmac('sha256', $data, $secret);

        if ($contentSignature !== $h1) {
            return false;
        }

        return true;
    }
}
