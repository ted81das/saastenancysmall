<?php

namespace App\Filament\Dashboard\Resources\SubscriptionResource\Pages;

use App\Constants\SubscriptionStatus;
use App\Filament\Dashboard\Resources\SubscriptionResource;
use App\Filament\Dashboard\Resources\SubscriptionResource\ActionHandlers\DiscardSubscriptionCancellationActionHandler;
use App\Models\Subscription;
use App\Services\PaymentProviders\PaymentManager;
use App\Services\SubscriptionManager;
use Filament\Resources\Pages\ViewRecord;

class ViewSubscription extends ViewRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\ActionGroup::make([
                \Filament\Actions\Action::make('change-plan')
                    ->label(__('Change Plan'))
                    ->color('primary')
                    ->icon('heroicon-o-rocket-launch')
                    ->visible(function (Subscription $record): bool {
                        return $record->status === SubscriptionStatus::ACTIVE->value;
                    })
                    ->url(fn (Subscription $record): string => SubscriptionResource::getUrl('change-plan', ['record' => $record->uuid])),
                \Filament\Actions\Action::make('update-payment-details')
                    ->label(__('Update Payment Details'))
                    ->color('gray')
                    ->icon('heroicon-s-credit-card')
                    ->visible(fn (Subscription $record, SubscriptionManager $subscriptionManager): bool => $subscriptionManager->canEditSubscriptionPaymentDetails($record))
                    ->action(function (Subscription $record, PaymentManager $paymentManager) {
                        $paymentProvider = $paymentManager->getPaymentProviderBySlug($record->paymentProvider->slug);

                        redirect()->to($paymentProvider->getChangePaymentMethodLink($record));
                    }),
                \Filament\Actions\Action::make('add-discount')
                    ->label(__('Add Discount'))
                    ->color('gray')
                    ->icon('heroicon-s-tag')
                    ->visible(function (Subscription $record, SubscriptionManager $subscriptionManager): bool {
                        return $subscriptionManager->canAddDiscount($record);
                    })
                    ->url(fn (Subscription $record): string => SubscriptionResource::getUrl('add-discount', ['record' => $record->uuid])),
                \Filament\Actions\Action::make('cancel')
                    ->color('gray')
                    ->label(__('Cancel Subscription'))
                    ->icon('heroicon-m-x-circle')
                    ->url(fn (Subscription $record): string => SubscriptionResource::getUrl('cancel', ['record' => $record->uuid]))
                    ->visible(fn (Subscription $record, SubscriptionManager $subscriptionManager): bool => $subscriptionManager->canCancelSubscription($record)),
                \Filament\Actions\Action::make('discard-cancellation')
                    ->color('gray')
                    ->label(__('Discard Cancellation'))
                    ->icon('heroicon-m-x-circle')
                    ->action(function ($record, DiscardSubscriptionCancellationActionHandler $handler) {
                        $handler->handle($record);
                    })->visible(fn (Subscription $record, SubscriptionManager $subscriptionManager): bool => $subscriptionManager->canDiscardSubscriptionCancellation($record)),
            ])->button()->icon('heroicon-s-cog')->label(__('Manage Subscription')),
        ];
    }
}
