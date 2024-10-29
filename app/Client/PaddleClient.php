<?php

namespace App\Client;

use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class PaddleClient
{
    public function createProduct(string $name, ?string $description, string $taxCategory = 'standard'): Response
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer '.config('services.paddle.vendor_auth_code'),
        ])->post($this->getApiUrl('/products'), [
            'name' => $name,
            'description' => $description ?? $name,
            'tax_category' => $taxCategory,
        ]);
    }

    public function createPriceForPlan(
        string $productId,
        string $interval,
        int $frequency,
        int $amount,
        string $currencyCode,
        ?string $trialInterval = null,
        ?int $trialFrequency = null,
        int $maxQuantity = 1,
    ): Response {

        $priceObject = [
            'product_id' => $productId,
            'description' => 'Subscription '.$interval.' '.$frequency,
            'billing_cycle' => [
                'interval' => $interval,
                'frequency' => $frequency,
            ],
            'unit_price' => [
                'amount' => (string) $amount,
                'currency_code' => $currencyCode,
            ],
            'quantity' => [
                'minimum' => 1,
                'maximum' => $maxQuantity,
            ],
        ];

        if ($trialInterval && $trialFrequency) {
            $priceObject['trial_period'] = [
                'interval' => $trialInterval,
                'frequency' => $trialFrequency,
            ];
        }

        return Http::withHeaders([
            'Authorization' => 'Bearer '.config('services.paddle.vendor_auth_code'),
        ])->post($this->getApiUrl('/prices'), $priceObject);
    }

    public function createPriceForOneTimeProduct(
        string $productId,
        int $amount,
        string $currencyCode,
        string $description,
        int $maxQuantity = 1,
    ): Response {

        $priceObject = [
            'product_id' => $productId,
            'description' => $description,
            'unit_price' => [
                'amount' => (string) $amount,
                'currency_code' => $currencyCode,
            ],
            'quantity' => [
                'minimum' => 1,
                'maximum' => $maxQuantity,
            ],
        ];

        return Http::withHeaders([
            'Authorization' => 'Bearer '.config('services.paddle.vendor_auth_code'),
        ])->post($this->getApiUrl('/prices'), $priceObject);
    }

    public function updateSubscriptionQuantity(
        string $paddleSubscriptionId,
        string $priceId,
        int $quantity,
        bool $isTrialing,
        bool $isProrated = true
    ): Response {
        $subscriptionObject = [
            'items' => [
                [
                    'price_id' => $priceId,
                    'quantity' => $quantity,
                ],
            ],
            'proration_billing_mode' => $isTrialing ? 'do_not_bill' : ($isProrated ? 'prorated_immediately' : 'full_immediately'),
        ];

        return Http::withHeaders([
            'Authorization' => 'Bearer '.config('services.paddle.vendor_auth_code'),
        ])->patch($this->getApiUrl('/subscriptions/'.$paddleSubscriptionId), $subscriptionObject);
    }

    public function getSubscription(string $paddleSubscriptionId): Response
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer '.config('services.paddle.vendor_auth_code'),
        ])->get($this->getApiUrl('/subscriptions/'.$paddleSubscriptionId));
    }

    public function updateSubscription(string $paddleSubscriptionId, string $priceId, bool $withProration, bool $isTrialing = false, int $quantity = 1): Response
    {
        $proration = $isTrialing ? 'do_not_bill' : ($withProration ? 'prorated_immediately' : 'full_immediately');
        $subscriptionObject = [
            'proration_billing_mode' => $proration,
            'items' => [
                [
                    'price_id' => $priceId,
                    'quantity' => $quantity,
                ],
            ],
        ];

        return Http::withHeaders([
            'Authorization' => 'Bearer '.config('services.paddle.vendor_auth_code'),
        ])->patch($this->getApiUrl('/subscriptions/'.$paddleSubscriptionId), $subscriptionObject);
    }

    public function addDiscountToSubscription(string $paddleSubscriptionId, string $paddleDiscountId, string $effectiveFrom = 'next_billing_period')
    {
        $subscriptionObject = [
            'discount' => [
                'id' => $paddleDiscountId,
                'effective_from' => $effectiveFrom,
            ],
        ];

        return Http::withHeaders([
            'Authorization' => 'Bearer '.config('services.paddle.vendor_auth_code'),
        ])->patch($this->getApiUrl('/subscriptions/'.$paddleSubscriptionId), $subscriptionObject);

    }

    public function cancelSubscription(string $paddleSubscriptionId)
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer '.config('services.paddle.vendor_auth_code'),
        ])->post($this->getApiUrl('/subscriptions/'.$paddleSubscriptionId.'/cancel'), ['cancel_at_end' => true]);
    }

    public function discardSubscriptionCancellation(string $paddleSubscriptionId)
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer '.config('services.paddle.vendor_auth_code'),
        ])->patch($this->getApiUrl('/subscriptions/'.$paddleSubscriptionId), [
            'scheduled_change' => null,
        ]);
    }

    public function createDiscount(
        string $amount,
        string $description,
        string $discountType,
        string $currencyCode,
        bool $isRecurring = false,
        ?int $maximumRecurringIntervals = null,
        ?Carbon $expiresAt = null,
    ) {
        $discountObject = [
            'amount' => $amount,
            'description' => $description,
            'type' => $discountType,
            'currency_code' => $currencyCode,
            'recur' => $isRecurring,
            'maximum_recurring_intervals' => $maximumRecurringIntervals,
            'expires_at' => $expiresAt?->toRfc3339String(),
        ];

        return Http::withHeaders([
            'Authorization' => 'Bearer '.config('services.paddle.vendor_auth_code'),
        ])->post($this->getApiUrl('/discounts'), $discountObject);
    }

    public function getPaymentMethodUpdateTransaction(
        string $paddleSubscriptionId,
    ) {
        return Http::withHeaders([
            'Authorization' => 'Bearer '.config('services.paddle.vendor_auth_code'),
        ])->get($this->getApiUrl('/subscriptions/'.$paddleSubscriptionId.'/update-payment-method-transaction'));
    }

    private function getApiUrl(string $endpoint): string
    {
        if (config('services.paddle.is_sandbox')) {
            return 'https://sandbox-api.paddle.com'.$endpoint;
        }

        return 'https://api.paddle.com'.$endpoint;
    }
}
