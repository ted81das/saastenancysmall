<?php

namespace App\Http\Controllers;

use App\Constants\TenancyPermissionConstants;
use App\Services\CalculationManager;
use App\Services\PaymentProviders\PaymentManager;
use App\Services\PlanManager;
use App\Services\SubscriptionManager;
use App\Services\TenantManager;
use App\Services\TenantPermissionManager;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(
        private PlanManager $planManager,
        private SubscriptionManager $subscriptionManager,
        private PaymentManager $paymentManager,
        private CalculationManager $calculationManager,
        private TenantPermissionManager $tenantPermissionManager,
        private TenantManager $tenantManager,
    ) {

    }

    public function changePlan(string $subscriptionUuid, string $newPlanSlug, string $tenantUuid, Request $request)
    {
        $user = auth()->user();

        $tenant = $this->tenantManager->getTenantByUuid($tenantUuid);

        if (! $this->tenantPermissionManager->tenantUserHasPermissionTo($tenant, $user, TenancyPermissionConstants::PERMISSION_UPDATE_SUBSCRIPTIONS)) {
            return redirect()->back()->with('error', __('You do not have permission to change plans.'));
        }

        $subscription = $this->subscriptionManager->findActiveByTenantAndSubscriptionUuid($tenant, $subscriptionUuid);

        if (! $subscription) {
            return redirect()->back()->with('error', __('You do not have an active subscription.'));
        }

        if ($subscription->plan->slug === $newPlanSlug) {
            return redirect()->back()->with('error', __('You are already subscribed to this plan.'));
        }

        $paymentProvider = $subscription->paymentProvider()->first();

        if (! $paymentProvider) {
            return redirect()->back()->with('error', __('Error finding payment provider.'));
        }

        $paymentProviderStrategy = $this->paymentManager->getPaymentProviderBySlug(
            $paymentProvider->slug
        );

        $newPlan = $this->planManager->getActivePlanBySlug($newPlanSlug);

        $isProrated = config('app.payment.proration_enabled', true);

        $totals = $this->calculationManager->calculateNewPlanTotals(
            $subscription,
            $newPlanSlug,
            $isProrated,
        );

        if ($request->isMethod('post')) {
            $result = $this->subscriptionManager->changePlan($subscription, $paymentProviderStrategy, $newPlanSlug, $isProrated);

            if ($result) {
                return redirect()->route('subscription.change-plan.thank-you');
            } else {
                return redirect()->route('home')->with('error', __('Error changing plan.'));
            }
        }

        return view('subscription.change', [
            'subscription' => $subscription,
            'newPlan' => $newPlan,
            'isProrated' => $isProrated,
            'user' => $user,
            'totals' => $totals,
        ]);
    }

    public function success()
    {
        return view('subscription.change-thank-you');
    }
}
