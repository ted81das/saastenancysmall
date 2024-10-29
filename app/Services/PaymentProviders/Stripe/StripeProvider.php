<?php

namespace App\Services\PaymentProviders\Stripe;

use App\Constants\DiscountConstants;
use App\Constants\PaymentProviderConstants;
use App\Filament\Dashboard\Resources\SubscriptionResource;
use App\Models\Discount;
use App\Models\OneTimeProduct;
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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class StripeProvider implements PaymentProviderInterface
{
    public function __construct(
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

        try {

            $stripeCustomerId = $this->findOrCreateStripeCustomer($user);
            $stripeProductId = $this->findOrCreateStripeSubscriptionProduct($plan, $paymentProvider);
            $stripePriceId = $this->findOrCreateStripeSubscriptionProductPrice($plan, $paymentProvider, $stripeProductId);

            $stripe = $this->getClient();

            $trialDays = 0;
            if ($plan->has_trial) {
                $trialDays = $this->subscriptionManager->calculateSubscriptionTrialDays($plan);
            }

            $currencyCode = $subscription->currency()->firstOrFail()->code;

            $sessionCreationObject = [
                'customer' => $stripeCustomerId,
                'success_url' => route('checkout.subscription.success'),
                'cancel_url' => route('checkout.subscription', ['planSlug' => $plan->slug]),
                'mode' => 'subscription',
                'line_items' => [[
                    'price' => $stripePriceId,
                    'quantity' => $quantity,
                ]],
                'subscription_data' => [
                    'metadata' => [
                        'subscription_uuid' => $subscription->uuid,
                    ],
                ],
            ];

            if ($trialDays > 0) {
                $sessionCreationObject['subscription_data']['trial_period_days'] = $trialDays;
            }

            if ($discount !== null) {
                $stripeDiscountId = $this->findOrCreateStripeDiscount($discount, $paymentProvider, $currencyCode);

                $sessionCreationObject['discounts'] = [
                    [
                        'coupon' => $stripeDiscountId,
                    ],
                ];
            }

            $session = $stripe->checkout->sessions->create($sessionCreationObject);

        } catch (ApiErrorException $e) {
            Log::error($e->getMessage());

            throw $e;
        }

        return $session->url;
    }

    public function initProductCheckout(Order $order, ?Discount $discount = null): array
    {
        // stripe does not need any initialization

        return [];
    }

    public function createProductCheckoutRedirectLink(Order $order, ?Discount $discount = null): string
    {
        $paymentProvider = $this->assertProviderIsActive();

        try {
            $stripe = $this->getClient();

            $stripeCustomerId = $this->findOrCreateStripeCustomer($order->user);

            $sessionCreationObject = [
                'customer' => $stripeCustomerId,
                'success_url' => route('checkout.product.success'),
                'cancel_url' => route('checkout.product'),
                'mode' => 'payment',
                'line_items' => [],
                'payment_intent_data' => [
                    'metadata' => [
                        'order_uuid' => $order->uuid,
                    ],
                ],
            ];

            foreach ($order->items()->get() as $item) {
                $product = $item->oneTimeProduct()->firstOrFail();
                $stripeProductId = $this->findOrCreateStripeOneTimeProduct($product, $paymentProvider);
                $stripePriceId = $this->findOrCreateStripeOneTimeProductPrice($product, $paymentProvider, $stripeProductId);

                $sessionCreationObject['line_items'][] = [
                    'price' => $stripePriceId,
                    'quantity' => $item->quantity,
                ];
            }

            if ($discount !== null) {  // rethink about that when adding support for cart checkout (multiple products checkout) as this discount will be applied to the whole cart (to all products)
                $stripeDiscountId = $this->findOrCreateStripeDiscount($discount, $paymentProvider, $order->currency()->firstOrFail()->code);

                $sessionCreationObject['discounts'] = [
                    [
                        'coupon' => $stripeDiscountId,
                    ],
                ];
            }

            $session = $stripe->checkout->sessions->create($sessionCreationObject);

        } catch (ApiErrorException $e) {
            Log::error($e->getMessage());

            throw $e;
        }

        return $session->url;
    }

    public function changePlan(
        Subscription $subscription,
        Plan $newPlan,
        bool $withProration = false
    ): bool {
        $paymentProvider = $this->assertProviderIsActive();

        try {

            $stripeProductId = $this->findOrCreateStripeSubscriptionProduct($newPlan, $paymentProvider);
            $stripePriceId = $this->findOrCreateStripeSubscriptionProductPrice($newPlan, $paymentProvider, $stripeProductId);

            $stripe = $this->getClient();

            $planPrice = $this->calculationManager->getPlanPrice($newPlan);

            $subscriptionItems = $stripe->subscriptionItems->all([
                'subscription' => $subscription->payment_provider_subscription_id,
            ]);

            // remove old items from subscription and add new ones
            $itemsToDelete = [];
            foreach ($subscriptionItems as $subscriptionItem) {
                $itemsToDelete[] = [
                    'id' => $subscriptionItem->id,
                    'deleted' => true,
                ];
            }

            $subscriptionUpdateObject = [
                'items' => array_merge($itemsToDelete, [
                    [
                        'price' => $stripePriceId,
                        'quantity' => $subscription->quantity,
                    ],
                ]),
            ];

            if ($withProration) {
                $subscriptionUpdateObject['proration_behavior'] = 'always_invoice';
            } else {
                $subscriptionUpdateObject['proration_behavior'] = 'none';
            }

            $stripe->subscriptions->update($subscription->payment_provider_subscription_id, $subscriptionUpdateObject);

            $this->subscriptionManager->updateSubscription($subscription, [
                'plan_id' => $newPlan->id,
                'price' => $planPrice->price,
                'currency_id' => $planPrice->currency_id,
                'interval_id' => $newPlan->interval_id,
                'interval_count' => $newPlan->interval_count,
            ]);

        } catch (ApiErrorException $e) {
            Log::error($e->getMessage());

            throw $e;
        }

        return true;
    }

    public function cancelSubscription(Subscription $subscription): bool
    {
        $paymentProvider = $this->assertProviderIsActive();

        try {
            $stripe = $this->getClient();

            $stripe->subscriptions->update($subscription->payment_provider_subscription_id, ['cancel_at_period_end' => true]);

        } catch (ApiErrorException $e) {
            Log::error($e->getMessage());

            return false;
        }

        return true;
    }

    public function discardSubscriptionCancellation(Subscription $subscription): bool
    {
        $paymentProvider = $this->assertProviderIsActive();

        try {
            $stripe = $this->getClient();

            $stripe->subscriptions->update($subscription->payment_provider_subscription_id, ['cancel_at_period_end' => false]);

        } catch (ApiErrorException $e) {
            Log::error($e->getMessage());

            return false;
        }

        return true;
    }

    public function getChangePaymentMethodLink(Subscription $subscription): string
    {
        $paymentProvider = $this->assertProviderIsActive();

        try {
            $stripe = $this->getClient();

            $portalConfigId = Cache::rememberForever('stripe.portal_configuration_id', function () use ($stripe) {
                $portal = $stripe->billingPortal->configurations->create([
                    'business_profile' => [
                        'headline' => __('Manage your subscription and payment details.'),
                    ],
                    'features' => [
                        'invoice_history' => ['enabled' => true],
                        'payment_method_update' => ['enabled' => true],
                        'customer_update' => ['enabled' => false],
                    ],
                ]);

                return $portal->id;
            });

            $portal = $stripe->billingPortal->sessions->create([
                'customer' => $subscription->user->stripeData()->firstOrFail()->stripe_customer_id,
                'return_url' => SubscriptionResource::getUrl(),
            ]);

        } catch (ApiErrorException $e) {
            Log::error($e->getMessage());

            return '/';
        }

        return $portal->url;
    }

    public function addDiscountToSubscription(Subscription $subscription, Discount $discount): bool
    {
        $paymentProvider = $this->assertProviderIsActive();

        try {
            $stripe = $this->getClient();

            $stripeDiscountId = $this->findOrCreateStripeDiscount($discount, $paymentProvider, $subscription->currency()->firstOrFail()->code);

            $stripe->subscriptions->update($subscription->payment_provider_subscription_id, [
                'coupon' => $stripeDiscountId,
            ]);

        } catch (ApiErrorException $e) {
            Log::error($e->getMessage());

            return false;
        }

        return true;
    }

    public function updateSubscriptionQuantity(Subscription $subscription, int $quantity, bool $isProrated = true): bool
    {
        $paymentProvider = $this->assertProviderIsActive();

        try {
            $stripe = $this->getClient();

            $stripe->subscriptions->update($subscription->payment_provider_subscription_id, [
                'quantity' => $quantity,
                'proration_behavior' => $isProrated ? 'always_invoice' : 'none',
            ]);

        } catch (ApiErrorException $e) {
            Log::error($e->getMessage());

            return false;
        }

        return true;
    }

    public function getSlug(): string
    {
        return PaymentProviderConstants::STRIPE_SLUG;
    }

    public function initSubscriptionCheckout(Plan $plan, Subscription $subscription, ?Discount $discount = null, int $quantity = 1): array
    {
        // stripe does not need any initialization

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

    private function getClient(): StripeClient
    {
        return new StripeClient(config('services.stripe.secret_key'));
    }

    private function findOrCreateStripeSubscriptionProduct(Plan $plan, PaymentProvider $paymentProvider): string
    {
        $stripeProductId = $this->planManager->getPaymentProviderProductId($plan, $paymentProvider);

        if ($stripeProductId !== null) {
            return $stripeProductId;
        }

        $stripe = $this->getClient();

        $stripeProductId = $stripe->products->create([
            'id' => $plan->slug.'-'.Str::random(),
            'name' => $plan->name,
            'description' => ! empty($plan->description) ? strip_tags($plan->description) : $plan->name,
        ])->id;

        $this->planManager->addPaymentProviderProductId($plan, $paymentProvider, $stripeProductId);

        return $stripeProductId;
    }

    private function findOrCreateStripeOneTimeProduct(OneTimeProduct $product, PaymentProvider $paymentProvider): string
    {
        $stripeProductId = $this->oneTimeProductManager->getPaymentProviderProductId($product, $paymentProvider);

        if ($stripeProductId !== null) {
            return $stripeProductId;
        }

        $stripe = $this->getClient();

        $stripeProductId = $stripe->products->create([
            'id' => $product->slug.'-'.Str::random(),
            'name' => $product->name,
            'description' => ! empty($product->description) ? strip_tags($product->description) : $product->name,
        ])->id;

        $this->oneTimeProductManager->addPaymentProviderProductId($product, $paymentProvider, $stripeProductId);

        return $stripeProductId;
    }

    private function findOrCreateStripeCustomer(User $user): string
    {
        $stripe = $this->getClient();

        $stripeCustomerId = null;
        $stripeData = $user->stripeData();
        if ($stripeData->count() > 0) {
            $stripeData = $stripeData->first();
            $stripeCustomerId = $stripeData->stripe_customer_id;
        }

        if ($stripeCustomerId === null) {
            $customer = $stripe->customers->create(
                [
                    'email' => $user->email,
                    'name' => $user->name,
                ]
            );
            $stripeCustomerId = $customer->id;

            if ($stripeData->count() > 0) {
                $stripeData = $stripeData->first();
                $stripeData->stripe_customer_id = $stripeCustomerId;
                $stripeData->save();
            } else {
                $user->stripeData()->create([
                    'stripe_customer_id' => $stripeCustomerId,
                ]);
            }
        }

        return $stripeCustomerId;
    }

    private function findOrCreateStripeDiscount(Discount $discount, PaymentProvider $paymentProvider, string $currencyCode): string
    {
        $stripeDiscountId = $this->discountManager->getPaymentProviderDiscountId($discount, $paymentProvider);

        if ($stripeDiscountId !== null) {
            return $stripeDiscountId;
        }

        $stripe = $this->getClient();

        $couponObject = [
            'name' => $discount->name,
        ];

        if ($discount->type == DiscountConstants::TYPE_FIXED) {
            $couponObject['amount_off'] = $discount->amount;
        } else {
            $couponObject['percent_off'] = $discount->amount;
        }

        $couponObject['currency'] = $currencyCode;

        if ($discount->duration_in_months !== null) {
            $couponObject['duration'] = 'repeating';
            $couponObject['duration_in_months'] = $discount->duration_in_months;
        } elseif ($discount->is_recurring) {
            $couponObject['duration'] = 'forever';
        } else {
            $couponObject['duration'] = 'once';
        }

        if ($discount->valid_until !== null) {
            $carbon = Carbon::parse($discount->valid_until);
            $couponObject['redeem_by'] = $carbon->timestamp;
        }

        $stripeCoupon = $stripe->coupons->create(
            $couponObject
        );

        $stripeDiscountId = $stripeCoupon->id;

        $this->discountManager->addPaymentProviderDiscountId($discount, $paymentProvider, $stripeDiscountId);

        return $stripeDiscountId;
    }

    private function findOrCreateStripeSubscriptionProductPrice(Plan $plan, PaymentProvider $paymentProvider, string $stripeProductId): string
    {
        $planPrice = $this->calculationManager->getPlanPrice($plan);

        $stripeProductPriceId = $this->planManager->getPaymentProviderPriceId($planPrice, $paymentProvider);

        if ($stripeProductPriceId !== null) {
            return $stripeProductPriceId;
        }

        $stripe = $this->getClient();

        $stripeProductPriceId = $stripe->prices->create([
            'product' => $stripeProductId,
            'unit_amount' => $planPrice->price,
            'currency' => $planPrice->currency()->firstOrFail()->code,
            'recurring' => [
                'interval' => $plan->interval()->firstOrFail()->date_identifier,
                'interval_count' => $plan->interval_count,
            ],
        ])->id;

        $this->planManager->addPaymentProviderPriceId($planPrice, $paymentProvider, $stripeProductPriceId);

        return $stripeProductPriceId;
    }

    private function findOrCreateStripeOneTimeProductPrice(OneTimeProduct $oneTimeProduct, PaymentProvider $paymentProvider, string $stripeProductId): string
    {
        $productPrice = $this->calculationManager->getOneTimeProductPrice($oneTimeProduct);

        $stripeProductPriceId = $this->oneTimeProductManager->getPaymentProviderPriceId($productPrice, $paymentProvider);

        if ($stripeProductPriceId !== null) {
            return $stripeProductPriceId;
        }

        $stripe = $this->getClient();

        $stripeProductPriceId = $stripe->prices->create([
            'product' => $stripeProductId,
            'unit_amount' => $productPrice->price,
            'currency' => $productPrice->currency()->firstOrFail()->code,
        ])->id;

        $this->oneTimeProductManager->addPaymentProviderPriceId($productPrice, $paymentProvider, $stripeProductPriceId);

        return $stripeProductPriceId;
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
