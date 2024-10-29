<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\User;
use App\Services\PaymentProviders\PaymentManager;

class SubscriptionDiscountManager
{
    public function __construct(
        private DiscountManager $discountManager,
        private SubscriptionManager $subscriptionManager,
        private PaymentManager $paymentManager,
    ) {

    }

    public function applyDiscount(Subscription $subscription, string $discountCode, User $user): bool
    {
        if (! $this->subscriptionManager->canAddDiscount($subscription) ||
            ! $this->discountManager->isCodeRedeemableForPlan($discountCode, $user, $subscription->plan)) {
            return false;
        }

        $discount = $this->discountManager->getActiveDiscountByCode($discountCode);

        $paymentProvider = $this->paymentManager->getPaymentProviderBySlug(
            $subscription->paymentProvider()->firstOrFail()->slug
        );

        $result = $paymentProvider->addDiscountToSubscription($subscription, $discount);

        if ($result) {
            $this->discountManager->redeemCodeForSubscription($discountCode, $user, $subscription->id);

            return true;
        }

        return false;
    }
}
