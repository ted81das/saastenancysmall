<?php

namespace App\Filament\Dashboard\Resources\SubscriptionResource\Pages;

use App\Filament\Dashboard\Resources\SubscriptionResource;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Route;

class ConfirmCancelSubscription extends Page
{
    protected static string $resource = SubscriptionResource::class;

    protected static string $view = 'filament.dashboard.resources.subscription-resource.pages.confirm-cancel-subscription';

    protected function getViewData(): array
    {
        $route = Route::current();
        $subscriptionUuid = $route->parameters['record'];

        return array_merge(parent::getViewData(), [
            'subscriptionUuid' => $subscriptionUuid,
        ]);
    }
}
