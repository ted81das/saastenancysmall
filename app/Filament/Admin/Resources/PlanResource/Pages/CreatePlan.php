<?php

namespace App\Filament\Admin\Resources\PlanResource\Pages;

use App\Filament\Admin\Resources\PlanResource;
use App\Filament\CrudDefaults;
use Filament\Resources\Pages\CreateRecord;

class CreatePlan extends CreateRecord
{
    use CrudDefaults;
    protected static string $resource = PlanResource::class;
}
