<?php

namespace App\View\Components\Plans;

use App\Services\PlanManager;
use App\Services\SubscriptionManager;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class All extends Component
{
    public function __construct(
        protected PlanManager $planManager,
        protected SubscriptionManager $subscriptionManager,
        public array $products = [],
        public bool $isGrouped = true,
        public string $preselectedInterval = '',
        public bool $calculateSavingRates = false,
        public ?string $currentSubscriptionUuid = null,
        public bool $showDefaultProduct = false,
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.plans.all', $this->calculateViewData());
    }

    protected function calculateViewData()
    {
        $plans = $this->planManager->getAllPlansWithPrices(
            $this->products,
        );

        return $this->enrichViewData([], $plans);
    }

    protected function enrichViewData(array $viewData, Collection $plans)
    {
        $viewData['plans'] = $plans;

        if ($this->showDefaultProduct) {
            $defaultProduct = $this->planManager->getDefaultProduct();

            if ($defaultProduct) {
                $viewData['defaultProduct'] = $defaultProduct;
            }
        }

        $viewData['isGrouped'] = $this->isGrouped;

        $groupedPlans = [];
        if ($viewData['isGrouped']) {
            $groupedPlans = $this->groupPlans($plans);
        }

        if ($this->calculateSavingRates) {
            $viewData['intervalSavingPercentage'] = $this->calculateSavingRates($groupedPlans);
        }

        $viewData['groupedPlans'] = $groupedPlans;

        if (! empty($this->preselectedInterval) && ! array_key_exists($this->preselectedInterval, $groupedPlans)) {
            $this->preselectedInterval = '';
        }

        $viewData['preselectedInterval'] = empty($this->preselectedInterval)
            ? array_key_first($groupedPlans) ?? ''
            : $this->preselectedInterval;

        return $viewData;
    }

    private function groupPlans(Collection $plans): array
    {
        $arrayPlans = $plans->all();
        // sort by price
        usort($arrayPlans, function ($a, $b) {
            return $a->prices->first()->price <=> $b->prices->first()->price;
        });

        // group plans by interval
        $groupedPlans = [];
        foreach ($arrayPlans as $plan) {
            $groupedPlans[$plan->interval->name][] = $plan;
        }

        return $groupedPlans;
    }

    protected function calculateSavingRates(array $groupedPlans)
    {
        // calculate average savings for each interval
        // sum up all prices for each interval, then calculate the average savings
        $LowestPriceForInterval = [];
        $intervalSavingPercentage = [];
        foreach ($groupedPlans as $interval => $plans) {
            $LowestPriceForInterval[$interval] = $plans[0]->prices->first()->price;
        }

        // sort by price
        uksort($LowestPriceForInterval, function ($a, $b) {
            return $a <=> $b;
        });

        // calculate imaginary prices for each interval compared to the lowest interval
        for ($i = 1; $i < count($LowestPriceForInterval); $i++) {
            $currentInterval = array_keys($LowestPriceForInterval)[$i];
            $firstInterval = array_keys($LowestPriceForInterval)[0];

            $currentPrice = $LowestPriceForInterval[$currentInterval];
            $firstPrice = $LowestPriceForInterval[$firstInterval];

            $imaginaryPrice = $this->calculatePriceForImaginaryInterval($firstPrice, $firstInterval, $currentInterval);

            $intervalSavingPercentage[$currentInterval] = (($imaginaryPrice - $currentPrice) / ($imaginaryPrice)) * 100;
        }

        return array_map(function ($saving) {
            return ceil($saving);
        }, $intervalSavingPercentage);
    }

    protected function calculatePriceForImaginaryInterval(int $currentPrice, string $currentInterval, string $imaginaryInterval): int
    {
        // per week (approximate)
        $intervalConversion = [
            'week' => 1,
            'month' => 4,
            'year' => 48,
        ];

        $currentIntervalInWeeks = $intervalConversion[$currentInterval];
        $imaginaryIntervalInWeeks = $intervalConversion[$imaginaryInterval];

        return intval(ceil($currentPrice * $imaginaryIntervalInWeeks / $currentIntervalInWeeks));
    }
}
