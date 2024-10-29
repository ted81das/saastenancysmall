<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\PaymentProvider;
use App\Models\Plan;
use App\Models\PlanPaymentProviderData;
use App\Models\PlanPrice;
use App\Models\PlanPricePaymentProviderData;
use App\Models\Product;
use Illuminate\Support\Collection;

class PlanManager
{
    public function getPaymentProviderProductId(Plan $plan, PaymentProvider $paymentProvider): ?string
    {
        $result = PlanPaymentProviderData::where('plan_id', $plan->id)
            ->where('payment_provider_id', $paymentProvider->id)
            ->first();

        if ($result) {
            return $result->payment_provider_product_id;
        }

        return null;
    }

    public function findByPaymentProviderProductId(PaymentProvider $paymentProvider, string $paymentProviderProductId): ?Plan
    {
        $result = PlanPaymentProviderData::where('payment_provider_id', $paymentProvider->id)
            ->where('payment_provider_product_id', $paymentProviderProductId)
            ->first();

        if ($result) {
            return Plan::find($result->plan_id);
        }

        return null;
    }

    public function addPaymentProviderProductId(Plan $plan, PaymentProvider $paymentProvider, string $paymentProviderProductId): void
    {
        PlanPaymentProviderData::create([
            'plan_id' => $plan->id,
            'payment_provider_id' => $paymentProvider->id,
            'payment_provider_product_id' => $paymentProviderProductId,
        ]);
    }

    public function getPaymentProviderPriceId(PlanPrice $planPrice, PaymentProvider $paymentProvider): ?string
    {
        $result = PlanPricePaymentProviderData::where('plan_price_id', $planPrice->id)
            ->where('payment_provider_id', $paymentProvider->id)
            ->first();

        if ($result) {
            return $result->payment_provider_price_id;
        }

        return null;
    }

    public function addPaymentProviderPriceId(PlanPrice $planPrice, PaymentProvider $paymentProvider, string $paymentProviderPriceId): void
    {
        PlanPricePaymentProviderData::create([
            'plan_price_id' => $planPrice->id,
            'payment_provider_id' => $paymentProvider->id,
            'payment_provider_price_id' => $paymentProviderPriceId,
        ]);
    }

    public function getActivePlanBySlug(string $slug): ?Plan
    {
        return Plan::where('slug', $slug)->where('is_active', true)->first();
    }

    public function getActivePlanById(int $id): ?Plan
    {
        return Plan::where('id', $id)->where('is_active', true)->first();
    }

    public function getAllActivePlans(?string $planType = null)
    {
        if ($planType) {
            return Plan::where('is_active', true)->where('type', $planType)->get();
        }

        return Plan::where('is_active', true)->get();
    }

    public function getDefaultProduct(): ?Product
    {
        return Product::where('is_default', true)->first();
    }

    public function getAllPlansWithPrices(array $productSlugs = [], ?string $planType = null): Collection
    {
        $defaultCurrency = config('app.default_currency');

        $defaultCurrencyObject = Currency::where('code', $defaultCurrency)->first();

        if (! $defaultCurrencyObject) {
            return new Collection();
        }

        if (count($productSlugs) > 0) {
            // only the plans that have default currency prices
            $result = Plan::where('is_active', true)
                ->with(['product' => function ($query) use ($productSlugs) {
                    $query->whereIn('slug', $productSlugs);
                }])
                ->whereHas('product', function ($query) use ($productSlugs) {
                    $query->whereIn('slug', $productSlugs);
                })
                ->whereHas('prices', function ($query) use ($defaultCurrencyObject) {
                    $query->where('currency_id', $defaultCurrencyObject->id);
                })
                ->with(['prices' => function ($query) use ($defaultCurrencyObject) {
                    $query->where('currency_id', $defaultCurrencyObject->id);
                }]);

            if ($planType) {
                $result->where('type', $planType);
            }

            return $result->get();

        }

        // only the plans that have default currency prices
        $result = Plan::where('is_active', true)
            ->whereHas('prices', function ($query) use ($defaultCurrencyObject) {
                $query->where('currency_id', $defaultCurrencyObject->id);
            })
            ->with(['prices' => function ($query) use ($defaultCurrencyObject) {
                $query->where('currency_id', $defaultCurrencyObject->id);
            }]);

        if ($planType) {
            $result->where('type', $planType);
        }

        return $result->get();
    }
}
