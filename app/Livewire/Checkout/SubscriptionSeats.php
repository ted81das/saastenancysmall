<?php

namespace App\Livewire\Checkout;

use App\Models\Plan;
use App\Services\PlanManager;
use App\Services\SessionManager;
use Livewire\Component;

class SubscriptionSeats extends Component
{
    public $planType;

    public $quantity;

    public $planSlug;
    public $maxQuantity;

    private SessionManager $sessionManager;

    private PlanManager $planManager;

    public function boot(SessionManager $sessionManager, PlanManager $planManager)
    {
        $this->sessionManager = $sessionManager;
        $this->planManager = $planManager;
    }

    public function mount(Plan $plan)
    {
        $this->planType = $plan->type;
        $this->planSlug = $plan->slug;
        $this->quantity = $this->sessionManager->getSubscriptionCheckoutDto()->quantity;
        $this->maxQuantity = $plan->max_users_per_tenant;
    }

    public function updatedQuantity(int $value)
    {
        $plan = $this->planManager->getActivePlanBySlug($this->planSlug);

        $maxRule = '';
        if ($plan->max_users_per_tenant > 0) {
            $maxRule = '|max:'.$plan->max_users_per_tenant;
        }

        $this->validate([
            'quantity' => 'required|integer|min:1'.$maxRule,
        ]);

        $subscriptionCheckoutDto = $this->sessionManager->getSubscriptionCheckoutDto();
        $subscriptionCheckoutDto->quantity = $value;
        $this->sessionManager->saveSubscriptionCheckoutDto($subscriptionCheckoutDto);

        $this->dispatch('calculations-updated')->to(SubscriptionTotals::class);
    }

    public function render()
    {
        return view('livewire.checkout.subscription-seats');
    }
}
