<?php

namespace App\Services;

use App\Constants\InvoiceStatus;
use App\Constants\TransactionStatus;
use App\Models\Invoice as InvoiceEntity;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use LaravelDaily\Invoices\Invoice;
use Parfaitementweb\FilamentCountryField\CountryResolver;

class InvoiceManager
{
    public function __construct(
        private CountryResolver $countryResolver,
    ) {

    }

    public function generate(Transaction $transaction, bool $regenerate = false)
    {
        if (!$this->canGenerateInvoices($transaction)) {
            return null;
        }

        $invoiceEntity = $this->findOrCreateInvoiceForTransaction($transaction);

        if (!$regenerate && $invoiceEntity->status === InvoiceStatus::RENDERED->value) {
            $storage = Storage::disk(config('invoices.disk'));

            if (!$storage->exists($invoiceEntity->filename)) {
                throw new \Exception(sprintf('Invoice file not found for transaction %s', $transaction->id));
            }

            return response()->file($storage->path($invoiceEntity->filename));
        }

        $orderNumber = $transaction?->order?->uuid;
        $subscriptionNumber = $transaction?->subscription?->uuid;
        $customFields = [
            'email' => $transaction->user->email,
        ];

        $orderItems = [];
        if ($orderNumber) {
            $customFields[__('Order')] = $orderNumber;

            foreach ($transaction->order->items as $item) {
                $orderItems[] = InvoiceItem::make($item->oneTimeProduct->name)
                    ->quantity($item->quantity)
                    ->formattedPricePerUnit(
                        money($item->price_per_unit, $item->currency->code)
                    );
            }
        } elseif ($subscriptionNumber) {
            $customFields[__('subscription')] = $subscriptionNumber;

            $subscription = $transaction->subscription;

            $itemName = $subscription->plan->name;

            if ($subscription->plan->has_trial) {
                $itemName .= ' - ' . $subscription->plan->trial_interval_count . ' ' . $subscription->plan->trialInterval()->firstOrFail()->name . ' ' . __('free trial included');
            }

            $orderItems[] = InvoiceItem::make($itemName)
                ->quantity(1)
                ->formattedPricePerUnit(
                    money($subscription->price, $subscription->currency->code)
                );
        }

        $customFields = $this->addAddressInfo($transaction->user, $customFields);

        $customer = new Buyer([
            'name' => $transaction->user->name,
            'custom_fields' => $customFields,
        ]);

        $invoice = Invoice::make()
            ->buyer($customer)
            ->formattedTotalAmount(
                money($transaction->amount, $transaction->currency->code)
            )
            ->sequence($invoiceEntity->id)
            ->date($invoiceEntity->created_at)
            ->dateFormat(config('app.date_format'))
            ->logo(public_path(config('app.logo.dark')));

        if ($transaction->total_tax > 0) {
            $invoice->formattedTotalTaxes(
                money($transaction->total_tax, $transaction->currency->code)
            );
        }

        if ($transaction->total_discount > 0) {
            $invoice->formattedTotalDiscount(
                money($transaction->total_discount, $transaction->currency->code)
            );
        }

        if (count($orderItems) > 0) {
            $invoice->addItems($orderItems);
        }

        $dateCreated = Carbon::parse($invoiceEntity->created_at);
        $filename = sprintf('%s/%s/%s', config('invoices.path'), $dateCreated->format('Y/m'), $invoiceEntity->uuid);

        $invoice->filename($filename);
        $invoice->save();

        $invoiceEntity->update([
            'status' => InvoiceStatus::RENDERED->value,
            'filename' => $filename . '.pdf',
        ]);

        return $invoice->stream();
    }

    private function addAddressInfo(User $user, array $customFields): array
    {
        $address = $user->address()->first();

        if (!$address) {
            return $customFields;
        }

        $addressPieces = [];

        if (!empty($address->address_line_1)) {
            $addressPieces[] = $address->address_line_1;
        }

        if (!empty($address->address_line_2)) {
            $addressPieces[] = $address->address_line_2;
        }

        if (!empty($address->city)) {
            $addressPieces[] = $address->city;
        }

        if (!empty($address->state)) {
            $addressPieces[] = $address->state;
        }

        if (!empty($address->zip)) {
            $addressPieces[] = $address->zip;
        }

        if (!empty($address->country_code)) {
            $addressPieces[] = $this->countryResolver->resolveCountryFromCode($address->country_code);
        }

        if (count($addressPieces) > 0) {
            $customFields[__('address')] = implode(", ", $addressPieces);
        }

        if (!empty($address->tax_number)) {
            $customFields[__('tax number')] = $address->tax_number;
        }

        return $customFields;
    }

    public function canGenerateInvoices(Transaction $transaction): bool
    {
        if (config('invoices.enabled', false) !== true) {
            return false;
        }

        if ($transaction->status != TransactionStatus::SUCCESS->value) {
            return false;
        }

        return true;
    }

    public function addInvoicePlaceholderForTransaction(Transaction $transaction)
    {
        $this->findOrCreateInvoiceForTransaction($transaction);
    }

    private function findOrCreateInvoiceForTransaction(Transaction $transaction): InvoiceEntity
    {
        return InvoiceEntity::firstOrCreate([
            'transaction_id' => $transaction->id,
        ], [
            'uuid' => Str::uuid(),
            'status' => InvoiceStatus::UNRENEDERED->value,
            'transaction_id' => $transaction->id,
        ]);
    }
}
