<?php

namespace App\Filament\Admin\Resources\RoadmapItemResource\Pages;

use App\Filament\Admin\Resources\RoadmapItemResource;
use App\Filament\CrudDefaults;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRoadmapItem extends EditRecord
{
    use CrudDefaults;

    protected static string $resource = RoadmapItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
