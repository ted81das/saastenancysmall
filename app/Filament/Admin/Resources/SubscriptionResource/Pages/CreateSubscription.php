<?php

namespace App\Filament\Admin\Resources\SubscriptionResource\Pages;

use App\Filament\Admin\Resources\SubscriptionResource;
use App\Filament\CrudDefaults;
use Filament\Resources\Pages\CreateRecord;

class CreateSubscription extends CreateRecord
{
    use CrudDefaults;
    protected static string $resource = SubscriptionResource::class;
}
