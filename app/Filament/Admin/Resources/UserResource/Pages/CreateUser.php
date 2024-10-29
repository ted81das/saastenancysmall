<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use App\Filament\CrudDefaults;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    use CrudDefaults;
    protected static string $resource = UserResource::class;
}
