<?php

namespace App\Filament\Admin\Resources\SubscriptionResource\Pages;

use App\Constants\SubscriptionStatus;
use App\Filament\Admin\Resources\SubscriptionResource;
use App\Filament\ListDefaults;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListSubscriptions extends ListRecords
{
    use ListDefaults;

    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            'active' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SubscriptionStatus::ACTIVE)),
            'inactive' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SubscriptionStatus::INACTIVE)),
            'pending' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SubscriptionStatus::PENDING)),
            'canceled' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SubscriptionStatus::CANCELED)),
            'past Due' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SubscriptionStatus::PAST_DUE)),
        ];
    }
}
