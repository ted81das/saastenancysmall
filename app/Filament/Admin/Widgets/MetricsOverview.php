<?php

namespace App\Filament\Admin\Widgets;

use App\Services\MetricsManager;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class MetricsOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        /** @var MetricsManager $metricsManager */
        $metricsManager = resolve(MetricsManager::class);

        $currentMrr = $metricsManager->calculateMRR(now());
        $previewMrr = $metricsManager->calculateMRR(Carbon::yesterday());
        $mrrDescription = '';
        $mrrIcon = '';
        $color = 'gray';

        if ($previewMrr) {
            $mrrDescription = $previewMrr == $currentMrr ? '' : ($previewMrr > $currentMrr ? __('decrease') : __('increase'));

            if (strlen($mrrDescription) > 0) {
                $mrrDescription = money(abs($currentMrr - $previewMrr), config('app.default_currency')).' '.$mrrDescription;
                $mrrIcon = $previewMrr > $currentMrr ? 'heroicon-m-arrow-down' : 'heroicon-m-arrow-up';
                $color = $previewMrr > $currentMrr ? 'danger' : 'success';
            }
        }

        return [
            Stat::make(
                __('MRR'),
                money($currentMrr, config('app.default_currency'))
            )->description($mrrDescription)
                ->descriptionIcon($mrrIcon)
                ->color($color)
                ->chart([7, 2, 10, 3, 15, 4, 17])  // just for decoration :)
            ,
            Stat::make(
                __('Active Subscriptions'),
                $metricsManager->getActiveSubscriptions()
            ),
            Stat::make(
                __('Total revenue'),
                $metricsManager->getTotalRevenue()
            ),
            Stat::make(
                __('Total user subscription conversion'),
                $metricsManager->getTotalCustomerConversion()
            )->description(__('subscribed / total users')),
            Stat::make(
                __('Total Transactions'),
                $metricsManager->getTotalTransactions()
            ),

            Stat::make(
                __('Total Users'),
                $metricsManager->getTotalUsers()
            ),
        ];
    }
}
