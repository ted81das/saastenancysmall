<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Currency;
use App\Services\MetricsManager;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;

class MonthlyRecurringRevenueChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

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

        $data = $metricsManager->calculateMRRChart($period, $startDate, $endDate);

        return [
            'datasets' => [
                [
                    'label' => 'MRR',
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
        return __('Monthly recurring revenue (MRR) overview');
    }

    public function getDescription(): string|Htmlable|null
    {
        return __('MRR takes into account only active subscriptions (no trials).');
    }

    protected function getOptions(): RawJs
    {
        $currentCurrency = config('app.default_currency');
        $currency = Currency::where('code', $currentCurrency)->first();
        $symbol = $currency->symbol;

        return RawJs::make(<<<JS
        {
            scales: {
                y: {
                    ticks: {
                        callback: (value) => '$symbol' + value.toFixed(2),
                    },
                },
            },
        }
    JS);
    }
}
