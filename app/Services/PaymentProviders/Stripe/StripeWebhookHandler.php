<?php

namespace App\Services\PaymentProviders\Stripe;

use App\Constants\OrderStatus;
use App\Constants\PaymentProviderConstants;
use App\Constants\SubscriptionStatus;
use App\Constants\TransactionStatus;
use App\Models\Currency;
use App\Models\PaymentProvider;
use App\Models\UserStripeData;
use App\Services\OrderManager;
use App\Services\SubscriptionManager;
use App\Services\TransactionManager;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StripeWebhookHandler
{
    public function __construct(
        private SubscriptionManager $subscriptionManager,
        private TransactionManager $transactionManager,
        private OrderManager $orderManager,
    ) {

    }

    public function handleWebhook(Request $request): JsonResponse
    {
        try {
            $event = $this->buildStripeEvent($request);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Invalid payload',
            ], 400);
        }

        $paymentProvider = PaymentProvider::where('slug', PaymentProviderConstants::STRIPE_SLUG)->firstOrFail();

        // docs on events: https://stripe.com/docs/billing/testing?dashboard-or-api=api

        if ($event->type == 'customer.subscription.created' ||
            $event->type == 'customer.subscription.updated' ||
            $event->type == 'customer.subscription.resumed' ||
            $event->type == 'customer.subscription.deleted' ||
            $event->type == 'customer.subscription.paused'
        ) {
            $subscriptionUuid = $event->data->object->metadata->subscription_uuid;
            $subscription = $this->subscriptionManager->findByUuidOrFail($subscriptionUuid);
            $stripeSubscriptionStatus = $event->data->object->status;
            $subscriptionStatus = $this->mapStripeSubscriptionStatusToSubscriptionStatus($stripeSubscriptionStatus);
            $endsAt = $event->data->object->current_period_end;
            $endsAt = Carbon::createFromTimestampUTC($endsAt)->toDateTimeString();
            $trialEndsAt = $event->data->object->trial_end ? Carbon::createFromTimestampUTC($event->data->object->trial_end)->toDateTimeString() : null;
            $cancelledAt = $event->data->object->canceled_at ? Carbon::createFromTimestampUTC($event->data->object->canceled_at)->toDateTimeString() : null;

            $this->subscriptionManager->updateSubscription($subscription, [
                'status' => $subscriptionStatus,
                'ends_at' => $endsAt,
                'payment_provider_subscription_id' => $event->data->object->id,
                'payment_provider_status' => $event->data->object->status,
                'payment_provider_id' => $paymentProvider->id,
                'trial_ends_at' => $trialEndsAt,
                'cancelled_at' => $cancelledAt,
                'quantity' => $event->data->object->quantity,
            ]);
        } elseif ($event->type == 'customer.subscription.trial_will_end') {
            // TODO send email to user

        } elseif ($event->type == 'invoice.created') {
            $subscriptionUuid = $event->data->object->subscription_details->metadata->subscription_uuid;
            $subscription = $this->subscriptionManager->findByUuidOrFail($subscriptionUuid);
            $currency = Currency::where('code', strtoupper($event->data->object->currency))->firstOrFail();
            $invoiceStatus = $event->data->object->status;

            $discount = $this->sumDiscountAmounts($event->data->object->total_discount_amounts ?? []);
            $tax = $this->sumTaxAmounts($event->data->object->total_tax_amounts ?? []);

            // create transaction

            $this->transactionManager->createForSubscription(
                $subscription,
                $event->data->object->amount_due,
                $tax,
                $discount,
                0,  // calculated when invoice is paid
                $currency,
                $paymentProvider,
                $event->data->object->id,
                $invoiceStatus,
                $this->mapInvoiceStatusToTransactionStatus($invoiceStatus),
            );
        } elseif ($event->type == 'invoice.finalized' ||
                    $event->type == 'invoice.paid' ||
                    $event->type == 'invoice.updated'
        ) {
            $invoiceStatus = $event->data->object->status;
            $paymentIntent = $event->data->object->payment_intent;
            $fees = $this->calculateFees($paymentIntent);
            // update transaction

            $this->transactionManager->updateTransactionByPaymentProviderTxId(
                $event->data->object->id,
                $invoiceStatus,
                $this->mapInvoiceStatusToTransactionStatus($invoiceStatus),
                null,
                null,
                $fees,
            );
        } elseif ($event->type == 'invoice.finalization_failed' ||
            $event->type == 'invoice.payment_failed' ||
            $event->type == 'invoice.payment_action_required'
        ) {
            $invoiceStatus = $event->data->object->status;
            // update transaction

            $errorReason = $event->data->object->last_payment_error->message ?? null;

            $this->transactionManager->updateTransactionByPaymentProviderTxId(
                $event->data->object->id,
                $invoiceStatus,
                $this->mapInvoiceStatusToTransactionStatus($invoiceStatus),
                $errorReason,
            );

            $subscriptionUuid = $event->data->object->subscription_details->metadata->subscription_uuid;
            $subscription = $this->subscriptionManager->findByUuidOrFail($subscriptionUuid);

            $this->subscriptionManager->handleInvoicePaymentFailed($subscription);

        } elseif ($event->type == 'customer.updated') {
            $defaultPaymentMethodId = $event->data->object->invoice_settings->default_payment_method;
            $stripeCustomerId = $event->data->object->id;

            UserStripeData::where('stripe_customer_id', $stripeCustomerId)->update([
                'stripe_payment_method_id' => $defaultPaymentMethodId,
            ]);

        } elseif ($event->type == 'payment_intent.succeeded' || $event->type == 'payment_intent.payment_failed') { // order event
            $paymentIntentId = $event->data->object->id;
            $orderUuid = $event->data->object->metadata?->order_uuid;

            if (! empty($orderUuid)) {
                $order = $this->orderManager->findByUuidOrFail($orderUuid);
                $fees = $this->calculateFees($paymentIntentId);
                $currency = Currency::where('code', strtoupper($event->data->object->currency))->firstOrFail();

                $transaction = $this->transactionManager->getTransactionByPaymentProviderTxId($paymentIntentId);

                $transactionStatus = $event->type == 'payment_intent.succeeded' ? TransactionStatus::SUCCESS : TransactionStatus::FAILED;

                DB::transaction(function () use ($order, $event, $transaction, $transactionStatus, $fees, $currency, $paymentProvider, $paymentIntentId) {
                    if ($transaction) {
                        $this->transactionManager->updateTransaction(
                            $transaction,
                            $event->data->object->status,
                            $transactionStatus,
                            null,
                            $event->data->object->amount,
                            $fees,
                        );
                    } else {
                        $this->transactionManager->createForOrder(
                            $order,
                            $event->data->object->amount,
                            0,
                            $order->total_discount_amount,
                            $fees,
                            $currency,
                            $paymentProvider,
                            $paymentIntentId,
                            $event->data->object->status,
                            $transactionStatus,
                        );
                    }

                    $orderStatus = $event->type == 'payment_intent.succeeded' ? OrderStatus::SUCCESS : OrderStatus::FAILED;

                    $this->orderManager->updateOrder($order, [
                        'status' => $orderStatus->value,
                        'total_amount_after_discount' => $event->data->object->amount,
                        'payment_provider_id' => $paymentProvider->id,
                    ]);
                });
            }
        } elseif ($event->type == 'charge.refunded') { // order event
            $paymentIntentId = $event->data->object->payment_intent;

            $orderUuid = $event->data->object->metadata?->order_uuid;

            if (! empty($orderUuid)) {

                $transaction = $this->transactionManager->getTransactionByPaymentProviderTxId($paymentIntentId);

                if ($transaction) {
                    $this->transactionManager->updateTransaction(
                        $transaction,
                        'refunded',
                        TransactionStatus::REFUNDED,
                    );

                    if ($transaction->order) {
                        $this->orderManager->updateOrder($transaction->order, [
                            'status' => OrderStatus::REFUNDED,
                        ]);
                    }
                }
            }
        } elseif (str_starts_with($event->type, 'charge.dispute.')) { // order event
            $paymentIntentId = $event->data->object->payment_intent;

            $transaction = $this->transactionManager->getTransactionByPaymentProviderTxId($paymentIntentId);

            if ($transaction) {
                $this->transactionManager->updateTransaction(
                    $transaction,
                    $event->data->object->status,
                    TransactionStatus::DISPUTED,
                );

                if ($transaction->order) {
                    $this->orderManager->updateOrder($transaction->order, [
                        'status' => OrderStatus::DISPUTED,
                    ]);
                }
            }
        }

        return response()->json();
    }

    private function mapInvoiceStatusToTransactionStatus(string $invoiceStatus): TransactionStatus
    {
        if ($invoiceStatus == 'paid') {
            return TransactionStatus::SUCCESS;
        }

        if ($invoiceStatus == 'void') {
            return TransactionStatus::FAILED;
        }

        if ($invoiceStatus == 'pending') {
            return TransactionStatus::PENDING;
        }

        if ($invoiceStatus == 'open') {
            return TransactionStatus::NOT_STARTED;
        }

        return TransactionStatus::NOT_STARTED;
    }

    private function mapStripeSubscriptionStatusToSubscriptionStatus(string $stripeSubscriptionStatus)
    {
        if ($stripeSubscriptionStatus == 'active' || $stripeSubscriptionStatus == 'trialing') {
            return SubscriptionStatus::ACTIVE;
        }

        if ($stripeSubscriptionStatus == 'past_due') {
            return SubscriptionStatus::PAST_DUE;
        }

        if ($stripeSubscriptionStatus == 'canceled') {
            return SubscriptionStatus::CANCELED;

        }

        if ($stripeSubscriptionStatus == 'paused') {
            return SubscriptionStatus::PAUSED;
        }

        return SubscriptionStatus::INACTIVE;

    }

    protected function buildStripeEvent(Request $request)
    {
        $this->setupClient();

        return \Stripe\Webhook::constructEvent(
            $request->getContent(),
            $request->header('Stripe-Signature'),
            config('services.stripe.webhook_signing_secret')
        );
    }

    private function setupClient()
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret_key'));
    }

    private function sumDiscountAmounts(array $stripeDiscounts): int
    {
        $discount = 0;

        foreach ($stripeDiscounts as $stripeDiscount) {
            $discount += $stripeDiscount->amount;
        }

        return $discount;
    }

    private function sumTaxAmounts(array $stripeTaxes): int
    {
        $tax = 0;

        foreach ($stripeTaxes as $stripeTax) {
            $tax += $stripeTax->amount;
        }

        return $tax;
    }

    protected function calculateFees($paymentIntentId)
    {
        if (! $paymentIntentId) {
            return null;
        }

        $paymentIntent = \Stripe\PaymentIntent::retrieve([
            'id' => $paymentIntentId,
            'expand' => ['latest_charge.balance_transaction'],
        ]);

        return $paymentIntent?->latest_charge?->balance_transaction?->fee ?? 0;
    }
}
