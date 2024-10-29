<?php

namespace App\Filament\Admin\Resources\DiscountResource\Pages;

use App\Filament\Admin\Resources\DiscountResource;
use App\Filament\CrudDefaults;
use Filament\Resources\Pages\CreateRecord;

class CreateDiscount extends CreateRecord
{
    use CrudDefaults;
    protected static string $resource = DiscountResource::class;
}
