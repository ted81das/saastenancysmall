<?php

namespace App\Livewire\Checkout;

use App\Constants\SessionConstants;
use App\Dto\TotalsDto;
use App\Models\Plan;
use App\Services\CalculationManager;
use App\Services\DiscountManager;
use App\Services\SessionManager;
use Livewire\Attributes\On;
use Livewire\Component;

class SubscriptionTotals extends Component
{
    public $page;

    public $planSlug;

    public $planHasTrial = false;

    public $subtotal;

    public $discountAmount;

    public $amountDue;

    public $currencyCode;

    public $code;

    private DiscountManager $discountManager;

    private CalculationManager $calculationManager;

    private SessionManager $sessionManager;

    public function boot(
        DiscountManager $discountManager,
        CalculationManager $calculationManager,
        SessionManager $sessionManager,
    ) {
        $this->discountManager = $discountManager;
        $this->calculationManager = $calculationManager;
        $this->sessionManager = $sessionManager;
    }

    public function mount(TotalsDto $totals, Plan $plan, $page)
    {
        $this->page = $page;
        $this->planSlug = $plan->slug;
        $this->planHasTrial = $plan->has_trial;
        $this->subtotal = $totals->subtotal;
        $this->discountAmount = $totals->discountAmount;
        $this->amountDue = $totals->amountDue;
        $this->currencyCode = $totals->currencyCode;
    }

    public function getCodeFromSession()
    {
        $subscriptionCheckoutDto = $this->sessionManager->getSubscriptionCheckoutDto();

        return $subscriptionCheckoutDto->discountCode;
    }

    public function add()
    {
        $code = $this->code;

        if ($code === null) {
            session()->flash('error', __('Please enter a discount code.'));

            return;
        }

        $plan = Plan::where('slug', $this->planSlug)->where('is_active', true)->firstOrFail();

        $isRedeemable = $this->discountManager->isCodeRedeemableForPlan($code, auth()->user(), $plan);

        if (! $isRedeemable) {
            session()->flash('error', __('This discount code is invalid.'));

            return;
        }

        $subscriptionCheckoutDto = $this->sessionManager->getSubscriptionCheckoutDto();

        $subscriptionCheckoutDto->discountCode = $code;
        $subscriptionCheckoutDto->planSlug = $this->planSlug;

        session()->put(SessionConstants::SUBSCRIPTION_CHECKOUT_DTO, $subscriptionCheckoutDto);

        $this->updateTotals();

        session()->flash('success', __('The discount code has been applied.'));
    }

    public function remove()
    {
        $subscriptionCheckoutDto = $this->sessionManager->getSubscriptionCheckoutDto();

        $subscriptionCheckoutDto->discountCode = null;

        $this->sessionManager->saveSubscriptionCheckoutDto($subscriptionCheckoutDto);

        session()->flash('success', __('The discount code has been removed.'));

        $this->updateTotals();
    }

    #[On('calculations-updated')]
    public function updateTotals()
    {
        $subscriptionCheckoutDto = $this->sessionManager->getSubscriptionCheckoutDto();

        $totals = $this->calculationManager->calculatePlanTotals(
            auth()->user(),
            $this->planSlug,
            $subscriptionCheckoutDto->discountCode,
            $subscriptionCheckoutDto->quantity,
        );

        $this->subtotal = $totals->subtotal;
        $this->discountAmount = $totals->discountAmount;
        $this->amountDue = $totals->amountDue;
        $this->currencyCode = $totals->currencyCode;
    }

    public function render()
    {
        return view('livewire.checkout.subscription-totals', [
            'addedCode' => $this->getCodeFromSession(),
        ]);
    }
}
