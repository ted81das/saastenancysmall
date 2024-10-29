<?php

namespace App\Filament\Admin\Resources\ProductResource\Pages;

use App\Filament\Admin\Resources\ProductResource;
use App\Filament\CrudDefaults;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    use CrudDefaults;

    protected static string $resource = ProductResource::class;
}
