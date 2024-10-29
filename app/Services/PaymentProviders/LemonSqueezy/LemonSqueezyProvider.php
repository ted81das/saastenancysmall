<?php

namespace App\Services\PaymentProviders\LemonSqueezy;

use App\Client\LemonSqueezyClient;
use App\Constants\DiscountConstants;
use App\Constants\PaymentProviderConstants;
use App\Constants\PlanType;
use App\Models\Discount;
use App\Models\Order;
use App\Models\PaymentProvider;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\CalculationManager;
use App\Services\DiscountManager;
use App\Services\OneTimeProductManager;
use App\Services\PaymentProviders\PaymentProviderInterface;
use App\Services\PlanManager;
use App\Services\SubscriptionManager;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LemonSqueezyProvider implements PaymentProviderInterface
{
    public function __construct(
        private LemonSqueezyClient $client,
        private SubscriptionManager $subscriptionManager,
        private CalculationManager $calculationManager,
        private PlanManager $planManager,
        private DiscountManager $discountManager,
        private OneTimeProductManager $oneTimeProductManager,
    ) {

    }

    public function createSubscriptionCheckoutRedirectLink(Plan $plan, Subscription $subscription, ?Discount $discount = null, int $quantity = 1): string
    {
        $paymentProvider = $this->assertProviderIsActive();

        /** @var User $user */
        $user = auth()->user();

        $variantId = $this->planManager->getPaymentProviderProductId($plan, $paymentProvider);

        if ($variantId === null) {
            Log::error('Failed to find variant ID for plan: (did you forget to add it to the plan?) '.$plan->id);
            throw new \Exception('Failed to find variant ID for plan');
        }

        $price = $this->calculationManager->getPlanPrice($plan);

        $object = [
            'custom_price' => $price->price,
            'product_options' => [
                'description' => $plan->description ?? $plan->name,
                'redirect_url' => route('checkout.subscription.success'),
                'enabled_variants' => [
                    $variantId,
                ],
            ],
            'checkout_options' => [
                'discount' => false,
            ],
            'checkout_data' => [
                'email' => $user->email,
                'name' => $user->name,
                'custom' => [
                    'subscription_uuid' => $subscription->uuid,
                ],
                'variant_quantities' => [
                    [
                        'variant_id' => intval($variantId),
                        'quantity' => $quantity,
                    ],
                ],
            ],
        ];

        if ($discount) {
            $object['checkout_data']['discount_code'] = $this->findOrCreateLemonSqueezyDiscount($discount, $paymentProvider);
        }

        $response = $this->client->createCheckout($object, $variantId);

        if (! $response->successful()) {
            Log::error('Failed to create lemon-squeezy checkout: '.$response->body());
            throw new \Exception('Failed to create lemon-squeezy checkout');
        }

        $redirectLink = $response->json()['data']['attributes']['url'] ?? null;

        if ($redirectLink === null) {
            Log::error('Failed to create lemon-squeezy checkout: '.$response->body());
            throw new \Exception('Failed to create lemon-squeezy checkout');
        }

        return $redirectLink;
    }

    public function initProductCheckout(Order $order, ?Discount $discount = null): array
    {
        // lemon squeezy does not need any initialization

        return [];
    }

    public function createProductCheckoutRedirectLink(Order $order, ?Discount $discount = null): string
    {
        $paymentProvider = $this->assertProviderIsActive();

        $variantIds = [];
        $variantQuantities = [];
        /** @var User $user */
        $user = auth()->user();

        $variantId = null;

        foreach ($order->items()->get() as $item) {
            $product = $item->oneTimeProduct()->firstOrFail();
            $variantId = $this->oneTimeProductManager->getPaymentProviderProductId($product, $paymentProvider);

            if ($variantId === null) {
                Log::error('Failed to find variant ID for product: (did you forget to add it to the product?) '.$product->id);
                throw new \Exception('Failed to find variant ID for product');
            }

            $variantQuantities[] = [
                'variant_id' => intval($variantId),
                'quantity' => $item->quantity,
            ];

            $variantIds[] = $variantId;
        }

        $object = [
            'custom_price' => $order->total_amount,
            'product_options' => [
                'redirect_url' => route('checkout.product.success'),
                'enabled_variants' => $variantIds,
            ],
            'checkout_options' => [
                'discount' => false,
            ],
            'checkout_data' => [
                'email' => $user->email,
                'name' => $user->name,
                'custom' => [
                    'order_uuid' => $order->uuid,
                ],
                'variant_quantities' => $variantQuantities,
            ],
        ];

        if ($discount) {
            $object['checkout_data']['discount_code'] = $this->findOrCreateLemonSqueezyDiscount($discount, $paymentProvider);
        }

        if ($variantId === null) {
            Log::error('Failed to find variant ID for product: (did you forget to add it to the product?) '.$product->id);
            throw new \Exception('Failed to find variant ID for product');
        }

        $response = $this->client->createCheckout($object, $variantId);

        if (! $response->successful()) {
            Log::error('Failed to create lemon-squeezy checkout: '.$response->body());
            throw new \Exception('Failed to create lemon-squeezy checkout');
        }

        $redirectLink = $response->json()['data']['attributes']['url'] ?? null;

        if ($redirectLink === null) {
            Log::error('Failed to create lemon-squeezy checkout: '.$response->body());
            throw new \Exception('Failed to create lemon-squeezy checkout');
        }

        return $redirectLink;
    }

    public function changePlan(
        Subscription $subscription,
        Plan $newPlan,
        bool $withProration = false
    ): bool {
        $paymentProvider = $this->assertProviderIsActive();

        try {

            $variantId = $this->planManager->getPaymentProviderProductId($newPlan, $paymentProvider);

            if ($variantId === null) {
                Log::error('Failed to find variant ID for plan while changing subscription plan: (did you forget to add it to the plan?) '.$newPlan->id);
                throw new \Exception('Failed to find variant ID for plan while changing subscription plan');
            }

            $planPrice = $this->calculationManager->getPlanPrice($newPlan);

            $response = $this->client->updateSubscription($subscription->payment_provider_subscription_id, $variantId, $withProration);

            if (! $response->successful()) {
                throw new \Exception('Failed to update lemon-squeezy subscription');
            }

            $subscription = $this->subscriptionManager->updateSubscription($subscription, [
                'plan_id' => $newPlan->id,
                'price' => $planPrice->price,
                'currency_id' => $planPrice->currency_id,
                'interval_id' => $newPlan->interval_id,
                'interval_count' => $newPlan->interval_count,
            ]);

            if ($subscription->plan->type === PlanType::SEAT_BASED->value) {
                // unfortunately, lemon-squeezy resets the quantity to 1 when changing the plan, so we need to update it again
                $this->updateSubscriptionQuantity($subscription, $subscription->quantity, $withProration);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            throw $e;
        }

        return true;
    }

    public function updateSubscriptionQuantity(Subscription $subscription, int $quantity, bool $isProrated = true): bool
    {
        $paymentProvider = $this->assertProviderIsActive();

        try {
            $lemonSqueezySubscription = $this->client->getSubscription($subscription->payment_provider_subscription_id)->json('data');
            $subscriptionItemId = $lemonSqueezySubscription['attributes']['first_subscription_item']['id'] ?? null;

            if ($subscriptionItemId === null) {
                throw new \Exception('Failed to get lemon-squeezy subscription item ID');
            }

            $response = $this->client->updateSubscriptionQuantity($subscriptionItemId, $quantity, $isProrated);

            if (! $response->successful()) {
                throw new \Exception('Failed to update lemon-squeezy subscription quantity');
            }

        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return false;
        }

        return true;
    }

    public function cancelSubscription(Subscription $subscription): bool
    {
        $paymentProvider = $this->assertProviderIsActive();

        try {
            $response = $this->client->cancelSubscription($subscription->payment_provider_subscription_id);

            if (! $response->successful()) {
                throw new \Exception('Failed to cancel lemon-squeezy subscription');
            }

        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return false;
        }

        return true;
    }

    public function discardSubscriptionCancellation(Subscription $subscription): bool
    {
        $paymentProvider = $this->assertProviderIsActive();

        try {
            $response = $this->client->discardSubscriptionCancellation($subscription->payment_provider_subscription_id);

            if (! $response->successful()) {
                throw new \Exception('Failed to discard lemon-squeezy subscription cancellation');
            }

        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return false;
        }

        return true;
    }

    public function getChangePaymentMethodLink(Subscription $subscription): string
    {
        $paymentProvider = $this->assertProviderIsActive();

        try {
            $response = $this->client->getSubscription($subscription->payment_provider_subscription_id);

            if (! $response->successful()) {
                throw new \Exception('Failed to get lemon-squeezy subscription');
            }

            $url = $response->json()['data']['attributes']['urls']['update_payment_method'] ?? '/';

            return $url;

        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return '/';
        }
    }

    public function addDiscountToSubscription(Subscription $subscription, Discount $discount): bool
    {
        throw new \Exception('It is not possible to add a discount to a lemon-squeezy subscription');
    }

    public function getSlug(): string
    {
        return PaymentProviderConstants::LEMON_SQUEEZY_SLUG;
    }

    public function initSubscriptionCheckout(Plan $plan, Subscription $subscription, ?Discount $discount = null, int $quantity = 1): array
    {
        // lemon squeezy does not need any initialization

        return [];
    }

    public function isRedirectProvider(): bool
    {
        return true;
    }

    public function isOverlayProvider(): bool
    {
        return false;
    }

    private function findOrCreateLemonSqueezyDiscount(Discount $discount, PaymentProvider $paymentProvider): string
    {
        $discountId = $this->discountManager->getPaymentProviderDiscountId($discount, $paymentProvider);

        if ($discountId !== null) {
            return $discountId;
        }

        $discountCode = $discount->codes()->first()->code ?? null;
        $discountCode = $discountCode ?? '';
        // remove any non-alphanumeric characters
        $discountCode = preg_replace('/[^A-Za-z0-9]/', '', $discountCode);

        $code = $discountCode.Str::random(16);

        $code = strtoupper($code);

        $duration = 'once';
        $durationInMonths = null;

        if ($discount->duration_in_months !== null) {
            $duration = 'repeating';
            $durationInMonths = $discount->duration_in_months;
        } elseif ($discount->is_recurring) {
            $duration = 'forever';
        }

        $response = $this->client->createDiscount(
            $discount->name,
            $code,
            intval($discount->amount),
            $discount->type === DiscountConstants::TYPE_FIXED ? 'fixed' : 'percent',
            $discount->max_redemptions > 0 ? $discount->max_redemptions : null,
            $duration,
            $durationInMonths,
            $discount->valid_until !== null ? Carbon::parse($discount->valid_until) : null,
        );

        if (! $response->successful()) {
            Log::error('Failed to create lemon-squeezy discount: '.$response->body());
            throw new \Exception('Failed to create lemon-squeezy discount');
        }

        $this->discountManager->addPaymentProviderDiscountId($discount, $paymentProvider, $code);

        return $code;
    }

    public function getName(): string
    {
        return PaymentProvider::where('slug', $this->getSlug())->firstOrFail()->name;
    }

    private function assertProviderIsActive(): PaymentProvider
    {
        $paymentProvider = PaymentProvider::where('slug', $this->getSlug())->firstOrFail();

        if ($paymentProvider->is_active === false) {
            throw new \Exception('Payment provider is not active: '.$this->getSlug());
        }

        return $paymentProvider;
    }

    public function supportsSeatBasedSubscriptions(): bool
    {
        return true;
    }
}
