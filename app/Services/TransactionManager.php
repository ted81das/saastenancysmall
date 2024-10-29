<?php

namespace App\Services;

use App\Constants\TransactionStatus;
use App\Models\Currency;
use App\Models\Order;
use App\Models\PaymentProvider;
use App\Models\Subscription;
use App\Models\Transaction;
use Illuminate\Support\Str;

class TransactionManager
{
    public function createForSubscription(
        Subscription $subscription,
        int $amount,
        int $totalTax,
        int $totalDiscount,
        int $totalFees,
        Currency $currency,
        PaymentProvider $paymentProvider,
        string $paymentProviderTransactionId,
        string $paymentProviderStatus,
        TransactionStatus $status = TransactionStatus::NOT_STARTED,
    ): Transaction {
        return Transaction::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $subscription->user_id,
            'currency_id' => $currency->id,
            'amount' => $amount,
            'total_tax' => $totalTax,
            'total_discount' => $totalDiscount,
            'total_fees' => $totalFees,
            'status' => $status->value,
            'subscription_id' => $subscription->id,
            'payment_provider_id' => $paymentProvider->id,
            'payment_provider_status' => $paymentProviderStatus,
            'payment_provider_transaction_id' => $paymentProviderTransactionId,
            'tenant_id' => $subscription->tenant_id,
        ]);
    }

    public function updateTransactionByPaymentProviderTxId(
        string $paymentProviderTransactionId,
        string $paymentProviderStatus,
        TransactionStatus $status,
        ?string $errorReason = null,
        ?int $newAmount = null,
        ?int $newFees = null,
    ): Transaction {
        $transaction = Transaction::where('payment_provider_transaction_id', $paymentProviderTransactionId)->firstOrFail();

        return $this->updateTransaction(
            $transaction,
            $paymentProviderStatus,
            $status,
            $errorReason,
            $newAmount,
            $newFees,
        );
    }

    public function updateTransaction(
        Transaction $transaction,
        string $paymentProviderStatus,
        TransactionStatus $status,
        ?string $errorReason = null,
        ?int $newAmount = null,
        ?int $newFees = null,
    ) {
        $data = [
            'status' => $status->value,
            'payment_provider_status' => $paymentProviderStatus,
        ];

        if ($newAmount !== null) {
            $data['amount'] = $newAmount;
        }

        if ($errorReason) {
            $data['error_reason'] = $errorReason;
        }

        if ($newFees !== null) {
            $data['total_fees'] = $newFees;
        }

        $transaction->update($data);

        return $transaction;
    }

    public function getTransactionByPaymentProviderTxId(string $paymentProviderTransactionId): ?Transaction
    {
        return Transaction::where('payment_provider_transaction_id', $paymentProviderTransactionId)->first();
    }

    public function createForOrder(
        Order $order,
        int $amount,
        int $totalTax,
        int $totalDiscount,
        int $totalFees,
        Currency $currency,
        PaymentProvider $paymentProvider,
        string $paymentProviderTransactionId,
        string $paymentProviderStatus,
        TransactionStatus $status = TransactionStatus::NOT_STARTED,
    ): Transaction {
        return $order->transactions()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $order->user_id,
            'currency_id' => $currency->id,
            'amount' => $amount,
            'total_tax' => $totalTax,
            'total_discount' => $totalDiscount,
            'total_fees' => $totalFees,
            'status' => $status->value,
            'payment_provider_id' => $paymentProvider->id,
            'payment_provider_status' => $paymentProviderStatus,
            'payment_provider_transaction_id' => $paymentProviderTransactionId,
            'tenant_id' => $order->tenant_id,
        ]);
    }
}
