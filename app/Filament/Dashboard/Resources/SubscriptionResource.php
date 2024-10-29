<?php

namespace App\Filament\Dashboard\Resources;

use App\Constants\DiscountConstants;
use App\Constants\PlanType;
use App\Constants\SubscriptionStatus;
use App\Filament\Dashboard\Resources\SubscriptionResource\ActionHandlers\DiscardSubscriptionCancellationActionHandler;
use App\Filament\Dashboard\Resources\SubscriptionResource\Pages;
use App\Mapper\SubscriptionStatusMapper;
use App\Models\Subscription;
use App\Services\ConfigManager;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-fire';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plan.name')->label(__('Plan')),
                Tables\Columns\TextColumn::make('price')->formatStateUsing(function (string $state, $record) {
                    $interval = $record->interval->name;
                    if ($record->interval_count > 1) {
                        $interval = $record->interval_count.' '.__(str()->of($record->interval->name)->plural()->toString());
                    }

                    if ($record->plan->type === PlanType::SEAT_BASED->value) {
                        $interval .= ' / '.__('seat');
                    }

                    return money($state, $record->currency->code).' / '.$interval;
                }),
                Tables\Columns\TextColumn::make('ends_at')->dateTime(config('app.datetime_format'))->label(__('Next Renewal')),
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(fn (string $state, SubscriptionStatusMapper $mapper): string => $mapper->mapForDisplay($state)),
                Tables\Columns\IconColumn::make('is_canceled_at_end_of_cycle')
                    ->label(__('Renews automatically'))
                    ->icon(function ($state) {
                        $state = boolval($state);

                        return $state ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle';
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label(__('View Details')),
                    Tables\Actions\Action::make('change-plan')
                        ->label(__('Change Plan'))
                        ->icon('heroicon-o-rocket-launch')
                        ->url(fn (Subscription $record): string => SubscriptionResource::getUrl('change-plan', ['record' => $record->uuid]))
                        ->visible(fn (Subscription $record): bool => $record->status === SubscriptionStatus::ACTIVE->value),
                    Tables\Actions\Action::make('cancel')
                        ->label(__('Cancel Subscription'))
                        ->icon('heroicon-m-x-circle')
                        ->visible(fn (Subscription $record): bool => ! $record->is_canceled_at_end_of_cycle && $record->status === SubscriptionStatus::ACTIVE->value)
                        ->url(fn (Subscription $record): string => SubscriptionResource::getUrl('cancel', ['record' => $record->uuid])),
                    Tables\Actions\Action::make('discard-cancellation')
                        ->label(__('Discard Cancellation'))
                        ->icon('heroicon-m-x-circle')
                        ->action(function ($record, DiscardSubscriptionCancellationActionHandler $handler) {
                            $handler->handle($record);
                        })->visible(fn (Subscription $record): bool => $record->is_canceled_at_end_of_cycle && $record->status === SubscriptionStatus::ACTIVE->value),
                ]),
            ])
            ->bulkActions([
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
            'view' => Pages\ViewSubscription::route('/{record}'),
            'change-plan' => Pages\ChangeSubscriptionPlan::route('/{record}/change-plan'),
            'cancel' => Pages\CancelSubscription::route('/{record}/cancel'),
            'confirm-cancellation' => Pages\ConfirmCancelSubscription::route('/{record}/confirm-cancellation'),
            'add-discount' => Pages\AddDiscount::route('/{record}/add-discount'),
            'paddle.update-payment-details' => Pages\PaymentProviders\Paddle\PaddleUpdatePaymentDetails::route('/paddle/update-payment-details'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('tenant_id', Filament::getTenant()->id);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function canUpdate(Model $record): bool
    {
        return false;
    }

    public static function canUpdateAny(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make(__('Subscription Details'))
                    ->description(__('View details about your subscription.'))
                    ->schema([
                        ViewEntry::make('status')
                            ->visible(fn (Subscription $record): bool => $record->status === SubscriptionStatus::PAST_DUE->value)
                            ->view('filament.common.infolists.entries.warning', [
                                'message' => __('Your subscription is past due. Please update your payment details.'),
                            ]),
                        TextEntry::make('plan.name'),
                        TextEntry::make('price')->formatStateUsing(function (string $state, $record) {
                            $interval = $record->interval->name;
                            if ($record->interval_count > 1) {
                                $interval = $record->interval_count.' '.__(str()->of($record->interval->name)->plural()->toString());
                            }

                            if ($record->plan->type === PlanType::SEAT_BASED->value) {
                                $interval .= ' / '.__('seat');
                            }

                            return money($state, $record->currency->code).' / '.$interval;
                        }),
                        TextEntry::make('ends_at')->dateTime(config('app.datetime_format'))->label(__('Next Renewal'))->visible(fn (Subscription $record): bool => ! $record->is_canceled_at_end_of_cycle),
                        TextEntry::make('status')
                            ->formatStateUsing(fn (string $state, SubscriptionStatusMapper $mapper): string => $mapper->mapForDisplay($state)),
                        TextEntry::make('is_canceled_at_end_of_cycle')
                            ->label(__('Renews automatically'))
                            ->icon(
                                function ($state) {
                                    $state = boolval($state);

                                    return $state ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle';
                                })
                            ->formatStateUsing(
                                function ($state) {
                                    return boolval($state) ? __('No') : __('Yes');
                                }),
                    ]),
                Section::make(__('Discount Details'))
                    ->hidden(fn (Subscription $record): bool => $record->discounts->isEmpty() ||
                        ($record->discounts[0]->valid_until !== null && $record->discounts[0]->valid_until < now())
                    )
                    ->description(__('View details about your discount.'))
                    ->schema([
                        TextEntry::make('discounts.amount')->formatStateUsing(function (string $state, $record) {
                            if ($record->discounts[0]->type === DiscountConstants::TYPE_PERCENTAGE) {
                                return $state.'%';
                            }

                            return money($state, $record->discounts[0]->code);
                        }),

                        TextEntry::make('discounts.valid_until')->dateTime(config('app.datetime_format'))->label(__('Valid Until')),
                    ]),

            ]);
    }

    public static function isDiscovered(): bool
    {
        return app()->make(ConfigManager::class)->get('app.customer_dashboard.show_subscriptions', true);
    }
}
