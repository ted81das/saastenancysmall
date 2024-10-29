<?php

namespace App\View\Components\Filament\Plans;

use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;

class All extends \App\View\Components\Plans\All
{
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|\Closure|string
    {
        return view('components.filament.plans.all', $this->calculateViewData());
    }

    protected function calculateViewData()
    {
        $subscription = null;
        if ($this->currentSubscriptionUuid !== null) {
            $subscription = $this->subscriptionManager->findActiveByTenantAndSubscriptionUuid(Filament::getTenant(), $this->currentSubscriptionUuid);
        }

        if ($subscription === null) {
            return [];
        }

        $plans = $this->planManager->getAllPlansWithPrices(
            $this->products,
            $subscription->plan->type,
        );

        $viewData['subscription'] = $subscription;

        return $this->enrichViewData($viewData, $plans);
    }
}
