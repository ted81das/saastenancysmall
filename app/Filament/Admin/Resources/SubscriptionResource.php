<?php

namespace App\Filament\Admin\Resources;

use App\Constants\DiscountConstants;
use App\Constants\PlanType;
use App\Constants\SubscriptionStatus;
use App\Filament\Admin\Resources\TenantResource\Pages\EditTenant;
use App\Mapper\SubscriptionStatusMapper;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationGroup = 'Revenue';

    protected static array $cachedSubscriptionHistoryComponents = [];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Subscription')
                    ->columnSpan('full')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make(__('Details'))
                            ->schema([
                                Forms\Components\Section::make()->schema([
                                    Forms\Components\Select::make('user_id')
                                        ->relationship('user', 'name')
                                        ->preload()
                                        ->required(),
                                    Forms\Components\Select::make('plan_id')
                                        ->relationship('plan', 'name')
                                        ->preload()
                                        ->required(),
                                    Forms\Components\TextInput::make('price')
                                        ->required(),
                                    Forms\Components\Select::make('currency_id')
                                        ->options(
                                            \App\Models\Currency::all()->sortBy('name')
                                                ->mapWithKeys(function ($currency) {
                                                    return [$currency->id => $currency->name.' ('.$currency->symbol.')'];
                                                })
                                                ->toArray()
                                        )
                                        ->label(__('Currency'))
                                        ->required(),
                                    Forms\Components\DateTimePicker::make('renew_at')->displayFormat(config('app.datetime_format')),
                                    Forms\Components\DateTimePicker::make('cancelled_at')->displayFormat(config('app.datetime_format')),
                                    Forms\Components\DateTimePicker::make('grace_period_ends_at')->displayFormat(config('app.datetime_format')),
                                    Forms\Components\Toggle::make('is_active')
                                        ->required(),
                                    Forms\Components\Toggle::make('is_trial_active')
                                        ->required(),
                                ]),
                            ]),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')->label(__('Tenant'))->searchable(),
                Tables\Columns\TextColumn::make('plan.name')->label(__('Plan'))->searchable(),
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
                Tables\Columns\TextColumn::make('payment_provider_id')
                    ->formatStateUsing(function (string $state, $record) {
                        return $record->paymentProvider->name;
                    })
                    ->label(__('Payment Provider'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => SubscriptionStatus::ACTIVE->value,
                    ])
                    ->formatStateUsing(
                        function (string $state, $record, SubscriptionStatusMapper $mapper) {
                            return $mapper->mapForDisplay($state);
                        })
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label(__('Created At'))
                    ->dateTime(config('app.datetime_format'))
                    ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->label(__('Updated At'))
                    ->dateTime(config('app.datetime_format'))
                    ->searchable()->sortable(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
            ])->defaultSort('created_at', 'desc');
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
            'index' => \App\Filament\Admin\Resources\SubscriptionResource\Pages\ListSubscriptions::route('/'),
            'view' => \App\Filament\Admin\Resources\SubscriptionResource\Pages\ViewSubscription::route('/{record}'),
        ];
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

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    /**
     * Filament schema is called multiple times for some reason, so we need to cache the components to avoid performance issues.
     */
    public static function subscriptionHistoryComponents($record): array
    {
        if (! empty(static::$cachedSubscriptionHistoryComponents)) {
            return static::$cachedSubscriptionHistoryComponents;
        }

        $i = 0;
        foreach ($record->versions->reverse() as $version) {
            $versionModel = $version->getModel();

            $user = $versionModel->user;
            $plan = $versionModel->plan;

            static::$cachedSubscriptionHistoryComponents[] = Section::make([
                TextEntry::make('plan_name_'.$i)
                    ->label(__('Plan'))
                    ->getStateUsing(fn () => $plan->name),

                TextEntry::make('status_'.$i)
                    ->label(__('Status'))
                    ->badge()
                    ->getStateUsing(fn () => $versionModel->status),

                TextEntry::make('changed_by_'.$i)
                    ->label(__('Changed By'))
                    ->getStateUsing(fn () => $user->name),

                TextEntry::make('ends_at_'.$i)
                    ->label(__('Ends At'))
                    ->getStateUsing(fn () => date(config('app.datetime_format'), strtotime($versionModel->ends_at))),

                TextEntry::make('payment_provider_status_'.$i)
                    ->label(__('Payment Provider Status'))
                    ->badge()
                    ->getStateUsing(fn () => $versionModel->payment_provider_status ?? '-'),

            ])->columns(5)->collapsible()->heading(
                date(config('app.datetime_format'), strtotime($version->created_at))
            );

            $i++;
        }

        return static::$cachedSubscriptionHistoryComponents;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Tabs::make('Subscription')
                    ->columnSpan('full')
                    ->tabs([
                        \Filament\Infolists\Components\Tabs\Tab::make(__('Details'))
                            ->schema([
                                Section::make(__('Subscription Details'))
                                    ->description(__('View details about subscription.'))
                                    ->schema([
                                        ViewEntry::make('status')
                                            ->visible(fn (Subscription $record): bool => $record->status === SubscriptionStatus::PAST_DUE->value)
                                            ->view('filament.common.infolists.entries.warning', [
                                                'message' => __('Subscription is past due.'),
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
                                        TextEntry::make('payment_provider_id')
                                            ->formatStateUsing(function (string $state, $record) {
                                                return $record->paymentProvider->name;
                                            })
                                            ->label(__('Payment Provider')),
                                        TextEntry::make('payment_provider_subscription_id')
                                            ->label(__('Payment Provider Subscription ID')),
                                        TextEntry::make('ends_at')->dateTime(config('app.datetime_format'))->label(__('Next Renewal'))->visible(fn (Subscription $record): bool => ! $record->is_canceled_at_end_of_cycle),
                                        TextEntry::make('trial_ends_at')->dateTime(config('app.datetime_format'))->label(__('Trial Ends At'))->visible(fn (Subscription $record): bool => $record->trial_ends_at !== null),
                                        TextEntry::make('status')
                                            ->badge()
                                            ->colors([
                                                'success' => SubscriptionStatus::ACTIVE->value,
                                            ])
                                            ->formatStateUsing(fn (string $state, SubscriptionStatusMapper $mapper): string => $mapper->mapForDisplay($state)),
                                        TextEntry::make('is_canceled_at_end_of_cycle')
                                            ->label(__('Renews automatically'))
                                            ->icon(function ($state) {
                                                $state = boolval($state);

                                                return $state ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle';
                                            })->formatStateUsing(
                                                function ($state) {
                                                    return boolval($state) ? __('No') : __('Yes');
                                                }),
                                        TextEntry::make('cancellation_reason')
                                            ->label(__('Cancellation Reason'))
                                            ->visible(fn (Subscription $record): bool => $record->cancellation_reason !== null),
                                        TextEntry::make('cancellation_additional_info')
                                            ->label(__('Cancellation Additional Info'))
                                            ->visible(fn (Subscription $record): bool => $record->cancellation_additional_info !== null),
                                        TextEntry::make('tenant.name')
                                            ->url(fn (Subscription $record) => EditTenant::getUrl(['record' => $record->tenant]))
                                            ->label(__('Tenant')),
                                        TextEntry::make('quantity')
                                            ->label(__('Quantity'))
                                            ->visible(fn (Subscription $record): bool => $record->plan->type === PlanType::SEAT_BASED->value),
                                    ]),
                                Section::make(__('Discount Details'))
                                    ->hidden(fn (Subscription $record): bool => $record->discounts->isEmpty() ||
                                        ($record->discounts[0]->valid_until !== null && $record->discounts[0]->valid_until < now())
                                    )
                                    ->description(__('View details about subscription discount.'))
                                    ->schema([
                                        TextEntry::make('discounts.amount')->formatStateUsing(function (string $state, $record) {
                                            if ($record->discounts[0]->type === DiscountConstants::TYPE_PERCENTAGE) {
                                                return $state.'%';
                                            }

                                            return money($state, $record->discounts[0]->code);
                                        }),

                                        TextEntry::make('discounts.valid_until')->dateTime(config('app.datetime_format'))->label(__('Valid Until')),
                                    ]),

                            ]),
                        \Filament\Infolists\Components\Tabs\Tab::make(__('Changes'))
                            ->schema(
                                function ($record) {
                                    // Filament schema is called multiple times for some reason, so we need to cache the components to avoid performance issues.
                                    return static::subscriptionHistoryComponents($record);
                                },
                            ),
                    ]),

            ]);

    }

    public static function getNavigationLabel(): string
    {
        return __('Subscriptions');
    }
}
