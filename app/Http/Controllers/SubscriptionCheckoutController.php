<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\CalculationManager;
use App\Services\DiscountManager;
use App\Services\PaymentProviders\PaymentManager;
use App\Services\SessionManager;
use App\Services\SubscriptionManager;
use Illuminate\Http\Request;

class SubscriptionCheckoutController extends Controller
{
    public function __construct(
        private PaymentManager $paymentManager,
        private DiscountManager $discountManager,
        private CalculationManager $calculationManager,
        private SubscriptionManager $subscriptionManager,
        private SessionManager $sessionManager,
    ) {

    }

    public function subscriptionCheckout(string $planSlug, Request $request)
    {
        $plan = Plan::where('slug', $planSlug)->where('is_active', true)->firstOrFail();
        $checkoutDto = $this->sessionManager->getSubscriptionCheckoutDto();

        if ($checkoutDto->planSlug !== $planSlug) {
            $checkoutDto = $this->sessionManager->resetSubscriptionCheckoutDto();
        }

        $checkoutDto->planSlug = $planSlug;

        $this->sessionManager->saveSubscriptionCheckoutDto($checkoutDto);

        $paymentProviders = $this->paymentManager->getActivePaymentProviders();
        $totals = $this->calculationManager->calculatePlanTotals(
            auth()->user(),
            $planSlug,
            $checkoutDto?->discountCode,
            $checkoutDto->quantity,
        );

        return view('checkout.subscription', [
            'paymentProviders' => $paymentProviders,
            'plan' => $plan,
            'totals' => $totals,
            'checkoutDto' => $checkoutDto,
            'successUrl' => route('checkout.subscription.success'),
        ]);
    }

    public function subscriptionCheckoutSuccess()
    {
        $checkoutDto = $this->sessionManager->getSubscriptionCheckoutDto();

        if ($checkoutDto->subscriptionId === null) {
            return redirect()->route('home');
        }

        $this->subscriptionManager->setAsPending($checkoutDto->subscriptionId);

        if ($checkoutDto->discountCode !== null) {
            $this->discountManager->redeemCodeForSubscription($checkoutDto->discountCode, auth()->user(), $checkoutDto->subscriptionId);
        }

        $this->sessionManager->resetSubscriptionCheckoutDto();

        return view('checkout.subscription-thank-you', []);
    }
}
