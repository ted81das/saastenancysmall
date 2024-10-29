<?php

namespace App\Filament\Dashboard\Resources\SubscriptionResource\Pages\PaymentProviders\Paddle;

use App\Filament\Dashboard\Resources\SubscriptionResource;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class PaddleUpdatePaymentDetails extends Page
{
    protected static string $resource = SubscriptionResource::class;

    protected static string $view = 'filament.dashboard.resources.subscription-resource.pages.payment-providers.paddle.update-payment-details';

    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'successUrl' => SubscriptionResource::getUrl(),
        ]);
    }

    public function getTitle(): string|Htmlable
    {
        return __('Update Payment Details');
    }
}
