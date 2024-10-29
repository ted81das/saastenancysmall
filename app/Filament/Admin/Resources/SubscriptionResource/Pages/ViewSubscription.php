<?php

namespace App\Filament\Admin\Resources\SubscriptionResource\Pages;

use App\Constants\PlanType;
use App\Constants\SubscriptionStatus;
use App\Filament\Admin\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\Services\PaymentProviders\PaymentManager;
use App\Services\PlanManager;
use App\Services\SubscriptionDiscountManager;
use App\Services\SubscriptionManager;
use App\Services\TenantManager;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewSubscription extends ViewRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\ActionGroup::make([
                \Filament\Actions\Action::make('sync')
                    ->label(__('Sync Quantity'))
                    ->icon('heroicon-o-arrow-path')
                    ->visible(function (Subscription $record): bool {
                        return $record->plan->type === PlanType::SEAT_BASED->value && $record->status === SubscriptionStatus::ACTIVE->value;
                    })
                    ->action(function (TenantManager $tenantManager, Subscription $record) {
                        $tenantManager->syncSubscriptionQuantity($record);
                    }),
                \Filament\Actions\Action::make('change-plan')
                    ->label(__('Change Plan'))
                    ->icon('heroicon-o-rocket-launch')
                    ->visible(function (Subscription $record): bool {
                        return $record->status === SubscriptionStatus::ACTIVE->value;
                    })
                    ->form([
                        \Filament\Forms\Components\Select::make('plan_id')
                            ->label(__('Plan'))
                            ->default($this->getRecord()->plan_id)
                            ->options(function (PlanManager $planManager, Subscription $record) {
                                return $planManager->getAllActivePlans($record->plan->type)->mapWithKeys(function ($plan) {
                                    return [$plan->id => $plan->name];
                                });
                            })
                            ->required()
                            ->helperText(__('Important: Plan change will happen immediately and depending on proration setting you set, user might be billed immediately full plan price or a proration is applied.')),
                    ])->action(function (array $data, SubscriptionManager $subscriptionManager, PlanManager $planManager, PaymentManager $paymentManager) {
                        $userSubscription = $this->getRecord();

                        $paymentProvider = $userSubscription->paymentProvider()->first();

                        if ($data['plan_id'] === $userSubscription->plan_id) {
                            Notification::make()
                                ->title(__('You need to select a different plan to change to.'))
                                ->danger()
                                ->send();

                            return;
                        }

                        $newPlanSlug = $planManager->getActivePlanById($data['plan_id'])->slug;

                        $paymentProviderStrategy = $paymentManager->getPaymentProviderBySlug(
                            $paymentProvider->slug
                        );

                        $isProrated = config('app.payment.proration_enabled', true);

                        $result = $subscriptionManager->changePlan($userSubscription, $paymentProviderStrategy, $newPlanSlug, $isProrated);

                        if ($result) {
                            Notification::make()
                                ->title(__('Plan change successful.'))
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title(__('Plan change failed.'))
                                ->danger()
                                ->send();
                        }
                    }),
                \Filament\Actions\Action::make('add-discount')
                    ->label(__('Add Discount'))
                    ->color('gray')
                    ->icon('heroicon-s-tag')
                    ->visible(function (Subscription $record, SubscriptionManager $subscriptionManager): bool {
                        return $subscriptionManager->canAddDiscount($record);
                    })
                    ->form([
                        \Filament\Forms\Components\TextInput::make('code')
                            ->label(__('Discount code'))
                            ->required(),
                    ])
                    ->action(function (array $data, Subscription $subscription, SubscriptionDiscountManager $subscriptionDiscountManager) {
                        $code = $data['code'];
                        $user = $subscription->user()->first();

                        $result = $subscriptionDiscountManager->applyDiscount($subscription, $code, $user);

                        if (! $result) {

                            Notification::make()
                                ->title(__('Could not apply discount code.'))
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title(__('Discount code has been applied.'))
                            ->send();
                    }),
                \Filament\Actions\Action::make('cancel')
                    ->color('gray')
                    ->label(__('Cancel Subscription'))
                    ->requiresConfirmation()
                    ->icon('heroicon-m-x-circle')
                    ->action(function (Subscription $userSubscription, SubscriptionManager $subscriptionManager, PaymentManager $paymentManager) {
                        $paymentProvider = $userSubscription->paymentProvider()->first();

                        $paymentProviderStrategy = $paymentManager->getPaymentProviderBySlug(
                            $paymentProvider->slug
                        );

                        $result = $subscriptionManager->cancelSubscription(
                            $userSubscription,
                            $paymentProviderStrategy,
                            __('Cancelled by admin.')
                        );

                        if ($result) {
                            Notification::make()
                                ->title(__('Subscription will be cancelled at the end of the billing period.'))
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title(__('Subscription cancellation failed.'))
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (Subscription $record, SubscriptionManager $subscriptionManager): bool => $subscriptionManager->canCancelSubscription($record)),
                \Filament\Actions\Action::make('discard-cancellation')
                    ->color('gray')
                    ->label(__('Discard Cancellation'))
                    ->icon('heroicon-m-x-circle')
                    ->requiresConfirmation()
                    ->action(function (Subscription $userSubscription, SubscriptionManager $subscriptionManager, PaymentManager $paymentManager) {

                        $paymentProvider = $userSubscription->paymentProvider()->first();

                        $paymentProviderStrategy = $paymentManager->getPaymentProviderBySlug(
                            $paymentProvider->slug
                        );

                        $result = $subscriptionManager->discardSubscriptionCancellation($userSubscription, $paymentProviderStrategy);

                        if ($result) {
                            Notification::make()
                                ->title(__('Subscription cancellation discarded'))
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title(__('Subscription cancellation discard failed.'))
                                ->danger()
                                ->send();
                        }
                    })->visible(fn (Subscription $record, SubscriptionManager $subscriptionManager): bool => $subscriptionManager->canDiscardSubscriptionCancellation($record)),
            ])->button()->icon('heroicon-s-cog')->label(__('Manage Subscription')),
        ];
    }
}
