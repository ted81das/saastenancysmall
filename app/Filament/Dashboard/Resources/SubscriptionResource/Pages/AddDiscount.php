<?php

namespace App\Filament\Dashboard\Resources\SubscriptionResource\Pages;

use App\Filament\Dashboard\Resources\SubscriptionResource;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Route;

class AddDiscount extends Page
{
    protected static string $resource = SubscriptionResource::class;

    protected static string $view = 'filament.dashboard.resources.subscription-resource.pages.add-discount';

    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'subscriptionUuid' => Route::current()->parameters['record'],
        ]);
    }
}
