<?php

namespace App\Livewire\Checkout;

use App\Exceptions\SubscriptionCreationNotAllowedException;
use App\Services\CheckoutManager;
use App\Services\DiscountManager;
use App\Services\PaymentProviders\PaymentManager;
use App\Services\PlanManager;
use App\Services\SessionManager;
use App\Services\UserManager;
use App\Validator\LoginValidator;
use App\Validator\RegisterValidator;

class SubscriptionCheckoutForm extends CheckoutForm
{
    public function checkout(
        LoginValidator $loginValidator,
        RegisterValidator $registerValidator,
        CheckoutManager $checkoutManager,
        PaymentManager $paymentManager,
        DiscountManager $discountManager,
        UserManager $userManager,
        PlanManager $planManager,
        SessionManager $sessionManager,
    ) {
        parent::handleLoginOrRegistration($loginValidator, $registerValidator, $userManager);

        $subscriptionCheckoutDto = $sessionManager->getSubscriptionCheckoutDto();
        $planSlug = $subscriptionCheckoutDto->planSlug;

        $plan = $planManager->getActivePlanBySlug($planSlug);

        if ($plan === null) {
            return redirect()->route('home');
        }

        $paymentProvider = $paymentManager->getPaymentProviderBySlug(
            $this->paymentProvider
        );

        $user = auth()->user();

        $discount = null;
        if ($subscriptionCheckoutDto->discountCode !== null) {
            $discount = $discountManager->getActiveDiscountByCode($subscriptionCheckoutDto->discountCode);
            $plan = $planManager->getActivePlanBySlug($planSlug);

            if (! $discountManager->isCodeRedeemableForPlan($subscriptionCheckoutDto->discountCode, $user, $plan)) {
                // this is to handle the case when user adds discount code that has max redemption limit per customer,
                // then logs-in during the checkout process and the discount code is not valid anymore
                $subscriptionCheckoutDto->discountCode = null;
                $discount = null;
                $this->dispatch('calculations-updated')->to(SubscriptionTotals::class);
            }
        }

        try {
            $subscription = $checkoutManager->initSubscriptionCheckout($planSlug, $subscriptionCheckoutDto->tenantUuid, $subscriptionCheckoutDto->quantity);
        } catch (SubscriptionCreationNotAllowedException $e) {
            return redirect()->route('checkout.subscription.already-subscribed');
        }

        $initData = $paymentProvider->initSubscriptionCheckout($plan, $subscription, $discount, $subscription->quantity);

        $subscriptionCheckoutDto->subscriptionId = $subscription->id;
        $sessionManager->saveSubscriptionCheckoutDto($subscriptionCheckoutDto);

        if ($paymentProvider->isRedirectProvider()) {
            $link = $paymentProvider->createSubscriptionCheckoutRedirectLink(
                $plan,
                $subscription,
                $discount,
                $subscription->quantity,
            );

            return redirect()->away($link);
        }

        $this->dispatch('start-overlay-checkout',
            paymentProvider: $paymentProvider->getSlug(),
            initData: $initData,
            successUrl: route('checkout.subscription.success'),
            email: $user->email,
            subscriptionUuid: $subscription->uuid,
        );
    }
}
