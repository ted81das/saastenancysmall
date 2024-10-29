<?php

namespace App\Services;

use App\Constants\PlanType;
use App\Constants\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\PaymentProviders\PaymentManager;

class TenantSubscriptionManager
{
    public function __construct(
        private PaymentManager $paymentManager,
    ) {

    }

    public function getTenantSubscriptions(Tenant $tenant)
    {
        return $tenant
            ->subscriptions()
            ->where('status', SubscriptionStatus::ACTIVE->value)
            ->with('plan')
            ->get();
    }

    public function updateSubscriptionQuantity(Subscription $subscription, int $quantity): bool
    {
        if ($subscription->plan->type !== PlanType::SEAT_BASED->value) {
            return true;
        }

        $isProrated = config('app.payment.proration_enabled', true);

        $paymentProvider = $this->paymentManager->getPaymentProviderBySlug(
            $subscription->paymentProvider->slug
        );

        return $paymentProvider->updateSubscriptionQuantity($subscription, $quantity, $isProrated);
    }
}
