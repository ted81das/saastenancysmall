<?php

namespace App\Filament\Admin\Resources\PlanResource\Pages;

use App\Filament\Admin\Resources\PlanResource;
use App\Filament\ListDefaults;
use Filament\Resources\Pages\ListRecords;

class ListPlans extends ListRecords
{
    use ListDefaults;

    protected static string $resource = PlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
