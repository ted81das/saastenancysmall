<?php

namespace App\Filament\Admin\Resources\TransactionResource\Widgets;

use App\Constants\TransactionStatus;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TransactionOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;
    protected function getStats(): array
    {
        return [
            Stat::make(
                __('Total'),
                money(Transaction::where('status', TransactionStatus::SUCCESS->value)->sum('amount'), config('app.default_currency'))
            ),
            Stat::make(
                __('Total fees'),
                money(Transaction::where('status', TransactionStatus::SUCCESS->value)->sum('total_fees'), config('app.default_currency'))
            ),
            Stat::make(
                __('Transaction count'),
                Transaction::count()
            ),
        ];
    }
}
