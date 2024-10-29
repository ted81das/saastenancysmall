<?php

namespace App\Filament\Admin\Widgets;

use App\Services\MetricsManager;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;

class AverageUserSubscriptionConversionChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;

    protected static ?string $pollingInterval = null;

    protected function getData(): array
    {
        $startDate = $this->filters['start_date'];
        $endDate = $this->filters['end_date'];
        $period = $this->filters['period'];

        // parse the dates to Carbon instances
        $startDate = $startDate ? Carbon::parse($startDate) : null;
        $endDate = $endDate ? Carbon::parse($endDate) : null;

        $metricsManager = resolve(MetricsManager::class);

        $data = $metricsManager->calculateAverageUserSubscriptionConversionChart($period, $startDate, $endDate);

        return [
            'datasets' => [
                [
                    'label' => 'Average User Subscription Conversion',
                    'data' => array_values($data),
                ],
            ],
            'labels' => array_keys($data),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public function getHeading(): string|Htmlable|null
    {
        return __('Average User Subscription Conversion');
    }

    public function getDescription(): string|Htmlable|null
    {
        return __('Average User Subscription Conversion is the % of users who subscribed to a plan to the total users.');
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
        {
            scales: {
                y: {
                    ticks: {
                        callback: (value) => value + '%',
                    },
                },
            },
        }
    JS);
    }
}
