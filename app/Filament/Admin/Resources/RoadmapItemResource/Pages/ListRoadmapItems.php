<?php

namespace App\Filament\Admin\Resources\RoadmapItemResource\Pages;

use App\Constants\RoadmapItemStatus;
use App\Filament\Admin\Resources\RoadmapItemResource;
use App\Filament\ListDefaults;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListRoadmapItems extends ListRecords
{
    use ListDefaults;

    protected static string $resource = RoadmapItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            RoadmapItemStatus::PENDING_APPROVAL->value => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', RoadmapItemStatus::PENDING_APPROVAL)),
            RoadmapItemStatus::APPROVED->value => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', RoadmapItemStatus::APPROVED)),
            RoadmapItemStatus::IN_PROGRESS->value => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', RoadmapItemStatus::IN_PROGRESS)),
            RoadmapItemStatus::COMPLETED->value => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', RoadmapItemStatus::COMPLETED)),
            RoadmapItemStatus::CANCELLED->value => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', RoadmapItemStatus::CANCELLED)),
            RoadmapItemStatus::REJECTED->value => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', RoadmapItemStatus::REJECTED)),
        ];
    }
}
