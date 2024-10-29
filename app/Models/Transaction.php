<?php

namespace App\Models;

use App\Constants\TransactionStatus;
use App\Services\InvoiceManager;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Mpociot\Versionable\VersionableTrait;

class Transaction extends Model
{
    use HasFactory, VersionableTrait;
    protected string $versionClass = TransactionVersion::class;

    protected $fillable = [
        'uuid',
        'user_id',
        'plan_id',
        'amount',
        'total_tax',
        'total_discount',
        'total_fees',
        'currency_id',
        'status',
        'payment_provider_id',
        'payment_provider_status',
        'payment_provider_transaction_id',
        'subscription_id',
        'error_reason',
        'order_id',
        'tenant_id',
    ];

    protected static function booted(): void
    {
        // for tax compliance purposes, making sure that the invoice is generated when the transaction is successful
        // in a chronological order with invoice serial number is important, so we make sure a placeholder is created
        // even if the invoice is not rendered yet
        static::created(function (Transaction $transaction) {
            /** @var InvoiceManager $invoiceManager */
            $invoiceManager = app(InvoiceManager::class);
            if ($transaction->status == TransactionStatus::SUCCESS->value) {
                try {
                    $invoiceManager->addInvoicePlaceholderForTransaction($transaction);
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                }
            }
        });

        static::updated(function (Transaction $transaction) {
            /** @var InvoiceManager $invoiceManager */
            $invoiceManager = app(InvoiceManager::class);
            if ($transaction->status == TransactionStatus::SUCCESS->value) {
                try {
                    $invoiceManager->addInvoicePlaceholderForTransaction($transaction);
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                }
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function paymentProvider(): BelongsTo
    {
        return $this->belongsTo(PaymentProvider::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

}
