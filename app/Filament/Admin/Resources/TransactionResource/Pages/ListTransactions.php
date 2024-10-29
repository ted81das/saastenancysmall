<?php

namespace App\Filament\Admin\Resources\TransactionResource\Pages;

use App\Constants\TransactionStatus;
use App\Filament\Admin\Resources\TransactionResource;
use App\Filament\Admin\Resources\TransactionResource\Widgets\TransactionOverview;
use App\Filament\ListDefaults;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTransactions extends ListRecords
{
    use ListDefaults;
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TransactionOverview::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            'success' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', TransactionStatus::SUCCESS)),
            'refunded' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', TransactionStatus::REFUNDED)),
            'failed' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', TransactionStatus::FAILED)),
            'pending' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', TransactionStatus::PENDING)),
            'disputed' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', TransactionStatus::DISPUTED)),
        ];
    }
}
