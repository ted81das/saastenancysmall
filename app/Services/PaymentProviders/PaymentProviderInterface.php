<?php

namespace App\Services\PaymentProviders;

use App\Models\Discount;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Subscription;

interface PaymentProviderInterface
{
    public function getSlug(): string;

    public function getName(): string;

    public function createSubscriptionCheckoutRedirectLink(Plan $plan, Subscription $subscription, ?Discount $discount = null, int $quantity = 1): string;

    public function createProductCheckoutRedirectLink(Order $order, ?Discount $discount = null): string;

    public function initSubscriptionCheckout(Plan $plan, Subscription $subscription, ?Discount $discount = null, int $quantity = 1): array;

    public function initProductCheckout(Order $order, ?Discount $discount = null): array;

    public function isRedirectProvider(): bool;

    public function supportsSeatBasedSubscriptions(): bool;

    public function isOverlayProvider(): bool;

    public function changePlan(Subscription $subscription, Plan $newPlan, bool $withProration = false): bool;

    public function cancelSubscription(Subscription $subscription): bool;

    public function discardSubscriptionCancellation(Subscription $subscription): bool;

    public function getChangePaymentMethodLink(Subscription $subscription): string;

    public function addDiscountToSubscription(Subscription $subscription, Discount $discount): bool;

    public function updateSubscriptionQuantity(Subscription $subscription, int $quantity, bool $isProrated = true): bool;
}
