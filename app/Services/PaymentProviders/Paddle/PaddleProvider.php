<?php

namespace App\Services\PaymentProviders\Paddle;

use App\Client\PaddleClient;
use App\Constants\DiscountConstants;
use App\Constants\PaddleConstants;
use App\Constants\PaymentProviderConstants;
use App\Constants\PlanType;
use App\Filament\Dashboard\Resources\SubscriptionResource\Pages\PaymentProviders\Paddle\PaddleUpdatePaymentDetails;
use App\Models\Currency;
use App\Models\Discount;
use App\Models\OneTimeProduct;
use App\Models\OneTimeProductPrice;
use App\Models\Order;
use App\Models\PaymentProvider;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Services\CalculationManager;
use App\Services\DiscountManager;
use App\Services\OneTimeProductManager;
use App\Services\PaymentProviders\PaymentProviderInterface;
use App\Services\PlanManager;
use App\Services\SubscriptionManager;
use Carbon\Carbon;

class PaddleProvider implements PaymentProviderInterface
{
    public function __construct(
        private SubscriptionManager $subscriptionManager,
        private PaddleClient $paddleClient,
        private PlanManager $planManager,
        private CalculationManager $calculationManager,
        private DiscountManager $discountManager,
        private OneTimeProductManager $oneTimeProductManager,
    ) {

    }

    public function initSubscriptionCheckout(Plan $plan, Subscription $subscription, ?Discount $discount = null, int $quantity = 1): array
    {
        $paymentProvider = $this->assertProviderIsActive();

        $paddleProductId = $this->planManager->getPaymentProviderProductId($plan, $paymentProvider);

        if ($paddleProductId === null) {
            $paddleProductId = $this->createPaddleProductForPlan($plan, $paymentProvider);
        }

        $currency = $subscription->currency()->firstOrFail();

        $planPrice = $this->calculationManager->getPlanPrice($plan);

        $paddlePrice = $this->planManager->getPaymentProviderPriceId($planPrice, $paymentProvider);

        if ($paddlePrice === null) {
            $paddlePrice = $this->createPaddlePriceForPlan($plan, $paddleProductId, $currency, $paymentProvider, $planPrice);
        }

        $results = [
            'productDetails' => [
                [
                    'paddleProductId' => $paddleProductId,
                    'paddlePriceId' => $paddlePrice,
                    'quantity' => $quantity,
                ],
            ],
        ];

        if ($discount !== null) {
            $paddleDiscountId = $this->findOrCreatePaddleDiscount($discount, $paymentProvider, $currency->code);
            $results['paddleDiscountId'] = $paddleDiscountId;
        }

        return $results;
    }

    public function changePlan(
        Subscription $subscription,
        Plan $newPlan,
        bool $withProration = false
    ): bool {
        $paymentProvider = $this->assertProviderIsActive();

        $paddleProductId = $this->planManager->getPaymentProviderProductId($newPlan, $paymentProvider);

        if ($paddleProductId === null) {
            $paddleProductId = $this->createPaddleProductForPlan($newPlan, $paymentProvider);
        }

        $currency = $subscription->currency()->firstOrFail();
        $planPrice = $this->calculationManager->getPlanPrice($newPlan);

        $paddlePrice = $this->planManager->getPaymentProviderPriceId($planPrice, $paymentProvider);

        if ($paddlePrice === null) {
            $paddlePrice = $this->createPaddlePriceForPlan($newPlan, $paddleProductId, $currency, $paymentProvider, $planPrice);
        }

        $response = $this->paddleClient->updateSubscription(
            $subscription->payment_provider_subscription_id,
            $paddlePrice,
            $withProration,
            $subscription->trial_ends_at !== null && Carbon::parse($subscription->trial_ends_at)->isFuture(),
            quantity: $subscription->quantity,
        );

        if ($response->failed()) {
            throw new \Exception('Failed to update paddle subscription: '.$response->body());
        }

        $this->subscriptionManager->updateSubscription($subscription, [
            'plan_id' => $newPlan->id,
            'price' => $planPrice->price,
            'currency_id' => $planPrice->currency_id,
            'interval_id' => $newPlan->interval_id,
            'interval_count' => $newPlan->interval_count,
        ]);

        return true;
    }

    public function cancelSubscription(Subscription $subscription): bool
    {
        $paymentProvider = $this->assertProviderIsActive();

        $response = $this->paddleClient->cancelSubscription($subscription->payment_provider_subscription_id);

        if ($response->failed()) {

            logger()->error('Failed to cancel paddle subscription: '.$subscription->payment_provider_subscription_id.' '.$response->body());

            return false;
        }

        return true;
    }

    public function discardSubscriptionCancellation(Subscription $subscription): bool
    {
        $paymentProvider = $this->assertProviderIsActive();

        $response = $this->paddleClient->discardSubscriptionCancellation($subscription->payment_provider_subscription_id);

        if ($response->failed()) {
            logger()->error('Failed to discard paddle subscription cancellation: '.$subscription->payment_provider_subscription_id.' '.$response->body());

            return false;
        }

        return true;
    }

    public function getChangePaymentMethodLink(Subscription $subscription): string
    {
        $paymentProvider = $this->assertProviderIsActive();

        $response = $this->paddleClient->getPaymentMethodUpdateTransaction($subscription->payment_provider_subscription_id);

        if ($response->failed()) {
            logger()->error('Failed to get paddle payment method update transaction: '.$subscription->payment_provider_subscription_id.' '.$response->body());

            throw new \Exception('Failed to get paddle payment method update transaction');
        }

        $responseBody = $response->json()['data'];
        $txId = $responseBody['id'];
        $url = PaddleUpdatePaymentDetails::getUrl();

        return $url.'?_ptxn='.$txId;
    }

    public function updateSubscriptionQuantity(Subscription $subscription, int $quantity, bool $isProrated = true): bool
    {
        $paymentProvider = $this->assertProviderIsActive();

        $plan = $subscription->plan()->firstOrFail();

        $planPrice = $this->calculationManager->getPlanPrice($plan);

        $priceId = $this->planManager->getPaymentProviderPriceId($planPrice, $paymentProvider);

        $isTrialing = $subscription->trial_ends_at !== null && Carbon::parse($subscription->trial_ends_at)->isFuture();

        $response = $this->paddleClient->updateSubscriptionQuantity($subscription->payment_provider_subscription_id, $priceId, $quantity, $isTrialing, $isProrated);

        if ($response->failed()) {
            logger()->error('Failed to update paddle subscription quantity: '.$subscription->payment_provider_subscription_id.' '.$response->body());

            return false;
        }

        return true;
    }

    public function initProductCheckout(Order $order, ?Discount $discount = null): array
    {
        $paymentProvider = $this->assertProviderIsActive();

        $results = [
            'productDetails' => [],
        ];

        $currency = $order->currency()->firstOrFail();

        foreach ($order->items()->get() as $item) {
            $product = $item->oneTimeProduct()->firstOrFail();
            $paddleProductId = $this->oneTimeProductManager->getPaymentProviderProductId($product, $paymentProvider);

            if ($paddleProductId === null) {
                $paddleProductId = $this->createPaddleProductForOneTimeProduct($product, $paymentProvider);
            }

            $oneTimeProductPrice = $this->calculationManager->getOneTimeProductPrice($product);

            $paddlePrice = $this->oneTimeProductManager->getPaymentProviderPriceId($oneTimeProductPrice, $paymentProvider);

            if ($paddlePrice === null) {
                $paddlePrice = $this->createPaddlePriceForOneTimeProduct($product, $paddleProductId, $currency, $paymentProvider, $oneTimeProductPrice);
            }

            $results['productDetails'][] = [
                'paddleProductId' => $paddleProductId,
                'paddlePriceId' => $paddlePrice,
                'quantity' => $item->quantity,
            ];
        }

        if ($discount !== null) {
            $paddleDiscountId = $this->findOrCreatePaddleDiscount($discount, $paymentProvider, $currency->code);
            $results['paddleDiscountId'] = $paddleDiscountId;
        }

        return $results;
    }

    public function createProductCheckoutRedirectLink(Order $order, ?Discount $discount = null): string
    {
        throw new \Exception('Not a redirect payment provider');
    }

    public function getSlug(): string
    {
        return PaymentProviderConstants::PADDLE_SLUG;
    }

    public function createSubscriptionCheckoutRedirectLink(Plan $plan, Subscription $subscription, ?Discount $discount = null, int $quantity = 1): string
    {
        throw new \Exception('Not a redirect payment provider');
    }

    public function isRedirectProvider(): bool
    {
        return false;
    }

    public function isOverlayProvider(): bool
    {
        return true;
    }

    public function getName(): string
    {
        return PaymentProvider::where('slug', $this->getSlug())->firstOrFail()->name;
    }

    private function findOrCreatePaddleDiscount(Discount $discount, PaymentProvider $paymentProvider, string $currencyCode)
    {
        $paddleDiscountId = $this->discountManager->getPaymentProviderDiscountId($discount, $paymentProvider);

        if ($paddleDiscountId !== null) {
            return $paddleDiscountId;
        }

        $amount = strval($discount->amount);

        $description = empty($discount->description) ? $discount->name : $discount->description;
        $discountType = $discount->type === DiscountConstants::TYPE_FIXED ? PaddleConstants::DISCOUNT_TYPE_FLAT : PaddleConstants::DISCOUNT_TYPE_PERCENTAGE;

        $response = $this->paddleClient->createDiscount(
            $amount,
            $description,
            $discountType,
            $currencyCode,
            $discount->is_recurring,
            $discount->maximum_recurring_intervals,
            $discount->valid_until !== null ? Carbon::parse($discount->valid_until) : null,
        );

        if ($response->failed()) {
            throw new \Exception('Failed to create paddle discount: '.$response->body());
        }

        $paddleDiscountId = $response->json()['data']['id'];

        $this->discountManager->addPaymentProviderDiscountId($discount, $paymentProvider, $paddleDiscountId);

        return $paddleDiscountId;
    }

    private function createPaddleProductForPlan(Plan $plan, PaymentProvider $paymentProvider): mixed
    {
        $createProductResponse = $this->paddleClient->createProduct(
            $plan->name,
            strip_tags($plan->product()->firstOrFail()->description),
            'standard'
        );

        if ($createProductResponse->failed()) {
            throw new \Exception('Failed to create paddle product: '.$createProductResponse->body());
        }

        $paddleProductId = $createProductResponse->json()['data']['id'];

        $this->planManager->addPaymentProviderProductId($plan, $paymentProvider, $paddleProductId);

        return $paddleProductId;
    }

    private function createPaddleProductForOneTimeProduct(OneTimeProduct $oneTimeProduct, PaymentProvider $paymentProvider): mixed
    {
        $createProductResponse = $this->paddleClient->createProduct(
            $oneTimeProduct->name,
            strip_tags($oneTimeProduct->description ?? $oneTimeProduct->name),
            'standard'
        );

        if ($createProductResponse->failed()) {
            throw new \Exception('Failed to create paddle product: '.$createProductResponse->body());
        }

        $paddleProductId = $createProductResponse->json()['data']['id'];

        $this->oneTimeProductManager->addPaymentProviderProductId($oneTimeProduct, $paymentProvider, $paddleProductId);

        return $paddleProductId;
    }

    private function createPaddlePriceForPlan(
        Plan $plan,
        string $paddleProductId,
        Currency $currency,
        PaymentProvider $paymentProvider,
        PlanPrice $planPrice
    ) {
        $trialInterval = null;
        $trialFrequency = null;

        if ($plan->has_trial) {
            $trialInterval = $plan->trialInterval()->firstOrFail()->date_identifier;
            $trialFrequency = $plan->trial_interval_count;
        }

        $maxQuantity = 1;
        if ($plan->type === PlanType::SEAT_BASED->value) {
            $maxQuantity = $plan->max_users_per_tenant > 0 ? $plan->max_users_per_tenant : 10000;
        }

        $response = $this->paddleClient->createPriceForPlan(
            $paddleProductId,
            $plan->interval()->firstOrFail()->date_identifier,
            $plan->interval_count,
            $planPrice->price,
            $currency->code,
            $trialInterval,
            $trialFrequency,
            $maxQuantity,
        );

        if ($response->failed()) {
            throw new \Exception('Failed to create paddle price: '.$response->body());
        }

        $paddlePrice = $response->json()['data']['id'];

        $this->planManager->addPaymentProviderPriceId($planPrice, $paymentProvider, $paddlePrice);

        return $paddlePrice;
    }

    private function createPaddlePriceForOneTimeProduct(
        OneTimeProduct $oneTimeProduct,
        string $paddleProductId,
        Currency $currency,
        PaymentProvider $paymentProvider,
        OneTimeProductPrice $oneTimeProductPrice
    ) {

        $response = $this->paddleClient->createPriceForOneTimeProduct(
            $paddleProductId,
            $oneTimeProductPrice->price,
            $currency->code,
            $oneTimeProduct->name,
        );

        if ($response->failed()) {
            throw new \Exception('Failed to create paddle price: '.$response->body());
        }

        $paddlePrice = $response->json()['data']['id'];

        $this->oneTimeProductManager->addPaymentProviderPriceId($oneTimeProductPrice, $paymentProvider, $paddlePrice);

        return $paddlePrice;
    }

    private function assertProviderIsActive(): PaymentProvider
    {
        $paymentProvider = PaymentProvider::where('slug', $this->getSlug())->firstOrFail();

        if ($paymentProvider->is_active === false) {
            throw new \Exception('Payment provider is not active: '.$this->getSlug());
        }

        return $paymentProvider;
    }

    public function addDiscountToSubscription(Subscription $subscription, Discount $discount): bool
    {
        $paymentProvider = $this->assertProviderIsActive();

        $currency = $subscription->currency()->firstOrFail();

        $paddleDiscountId = $this->findOrCreatePaddleDiscount($discount, $paymentProvider, $currency->code);

        $response = $this->paddleClient->addDiscountToSubscription(
            $subscription->payment_provider_subscription_id,
            $paddleDiscountId,
        );

        if ($response->failed()) {
            logger()->error('Failed to add paddle discount to subscription: '.$subscription->payment_provider_subscription_id.' '.$response->body());

            return false;
        }

        return true;
    }

    public function supportsSeatBasedSubscriptions(): bool
    {
        return false;
    }
}
