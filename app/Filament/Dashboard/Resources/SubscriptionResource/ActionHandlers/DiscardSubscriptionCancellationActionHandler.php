<?php

namespace App\Filament\Dashboard\Resources\SubscriptionResource\ActionHandlers;

use App\Constants\TenancyPermissionConstants;
use App\Filament\Dashboard\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\Services\PaymentProviders\PaymentManager;
use App\Services\SubscriptionManager;
use App\Services\TenantPermissionManager;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;

class DiscardSubscriptionCancellationActionHandler
{
    public function __construct(
        private SubscriptionManager $subscriptionManager,
        private PaymentManager $paymentManager,
        private TenantPermissionManager $tenantPermissionManager,
    ) {

    }

    public function handle(Subscription $record)
    {
        $user = auth()->user();

        $tenant = Filament::getTenant();

        if (! $this->tenantPermissionManager->tenantUserHasPermissionTo($tenant, $user, TenancyPermissionConstants::PERMISSION_UPDATE_SUBSCRIPTIONS)) {
            Notification::make()
                ->title(__('You do not have permission to cancel subscriptions.'))
                ->danger()
                ->send();

            return redirect()->to(SubscriptionResource::getUrl());
        }

        $subscription = $this->subscriptionManager->findActiveByTenantAndSubscriptionUuid($tenant, $record->uuid);

        if (! $subscription) {
            Notification::make()
                ->title(__('Error canceling subscription'))
                ->danger()
                ->send();

            return redirect()->to(SubscriptionResource::getUrl());
        }

        $paymentProvider = $subscription->paymentProvider()->first();

        $paymentProviderStrategy = $this->paymentManager->getPaymentProviderBySlug(
            $paymentProvider->slug
        );

        $this->subscriptionManager->discardSubscriptionCancellation($subscription, $paymentProviderStrategy);

        Notification::make()
            ->title(__('Subscription cancellation discarded'))
            ->success()
            ->send();

        return redirect()->to(SubscriptionResource::getUrl());
    }
}
