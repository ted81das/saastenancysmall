<?php

namespace App\Filament\Admin\Resources;

use App\Constants\TransactionStatus;
use App\Filament\Admin\Resources\OrderResource\Pages\ViewOrder;
use App\Filament\Admin\Resources\SubscriptionResource\Pages\ViewSubscription;
use App\Filament\Admin\Resources\TenantResource\Pages\EditTenant;
use App\Filament\Admin\Resources\TransactionResource\Pages;
use App\Filament\Admin\Resources\TransactionResource\Widgets\TransactionOverview;
use App\Mapper\TransactionStatusMapper;
use App\Models\Transaction;
use App\Services\InvoiceManager;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionResource extends Resource
{
    protected static array $cachedTransactionHistoryComponents = [];

    protected static ?string $model = Transaction::class;

    protected static ?string $navigationGroup = 'Revenue';

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
                Tables\Columns\TextColumn::make('tenant.name')->label(__('Tenant'))->searchable(),
                Tables\Columns\TextColumn::make('amount')->formatStateUsing(function (string $state, $record) {
                    return money($state, $record->currency->code);
                }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => TransactionStatus::SUCCESS->value,
                        'danger' => TransactionStatus::FAILED->value,
                    ])
                    ->formatStateUsing(function (string $state, $record, TransactionStatusMapper $mapper) {
                        return $mapper->mapForDisplay($state);
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_provider_id')
                    ->label(__('Payment Provider'))
                    ->getStateUsing(fn (Transaction $record) => $record->paymentProvider->name)
                    ->sortable(),
                Tables\Columns\TextColumn::make('owner')
                    ->label(__('Owner'))
                    ->getStateUsing(fn (Transaction $record) => $record->subscription_id !== null ? ($record->subscription->plan?->name ?? '-') : ($record->order_id !== null ? __('Order Nr. ').$record->order_id : '-'))
                    ->url(fn (Transaction $record) => $record->subscription_id !== null ? ViewSubscription::getUrl(['record' => $record->subscription]) : ($record->order_id !== null ? ViewOrder::getUrl(['record' => $record->order]) : '-')),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Updated At'))
                    ->dateTime(config('app.datetime_format'))
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('see-invoice')
                        ->label(__('See Invoice'))
                        ->icon('heroicon-o-document')
                        ->visible(fn (Transaction $record, InvoiceManager $invoiceManager): bool => $invoiceManager->canGenerateInvoices($record))
                        ->url(
                            fn (Transaction $record): string => route('invoice.generate', ['transactionUuid' => $record->uuid]),
                            shouldOpenInNewTab: true
                        ),
                    Tables\Actions\Action::make('force-regenerate')
                        ->label(__('Force Regenerate Invoice'))
                        ->color('gray')
                        ->icon('heroicon-o-arrow-path')
                        ->visible(fn (Transaction $record, InvoiceManager $invoiceManager): bool => $invoiceManager->canGenerateInvoices($record))
                        ->url(
                            function (Transaction $record): string {
                                return route('invoice.generate', ['transactionUuid' => $record->uuid, 'regenerate' => true]);
                            },
                            shouldOpenInNewTab: true
                        ),
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
            'index' => Pages\ListTransactions::route('/'),
            'view' => Pages\ViewTranscription::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Tabs::make('Transaction')
                    ->columnSpan('full')
                    ->tabs([
                        \Filament\Infolists\Components\Tabs\Tab::make(__('Details'))
                            ->icon('heroicon-s-currency-dollar')
                            ->schema([
                                TextEntry::make('uuid')->copyable(),
                                TextEntry::make('tenant')->getStateUsing(function (Transaction $record) {
                                    return $record->tenant->name;
                                })->url(fn (Transaction $record) => EditTenant::getUrl(['record' => $record->tenant])),
                                TextEntry::make('subscription_id')
                                    ->label(__('Subscription'))
                                    ->visible(fn (Transaction $record) => $record->subscription_id !== null)
                                    ->formatStateUsing(function (string $state, $record) {
                                        return $record->subscription->plan?->name ?? '-';
                                    })
                                    ->url(fn (Transaction $record) => $record->subscription ? ViewSubscription::getUrl(['record' => $record->subscription]) : '-')->badge(),
                                TextEntry::make('status')
                                    ->colors([
                                        'success' => TransactionStatus::SUCCESS->value,
                                        'danger' => TransactionStatus::FAILED->value,
                                    ])
                                    ->formatStateUsing(function (string $state, $record, TransactionStatusMapper $mapper) {
                                        return $mapper->mapForDisplay($state);
                                    })
                                    ->badge(),
                                TextEntry::make('payment_provider_transaction_id')->copyable(),
                                TextEntry::make('error_reason')->visible(fn (Transaction $record) => $record->error_reason !== null),
                                TextEntry::make('payment_provider_id')->label(__('Payment Provider'))->getStateUsing(fn (Transaction $record) => $record->paymentProvider->name),
                                TextEntry::make('payment_provider_status')->badge(),
                                TextEntry::make('amount')->formatStateUsing(function (string $state, $record) {
                                    return money($state, $record->currency->code);
                                }),
                                TextEntry::make('total_discount')->formatStateUsing(function (string $state, $record) {
                                    return money($state, $record->currency->code);
                                }),
                                TextEntry::make('total_fees')->formatStateUsing(function (string $state, $record) {
                                    return money($state, $record->currency->code);
                                }),
                                TextEntry::make('total_tax')->formatStateUsing(function (string $state, $record) {
                                    return money($state, $record->currency->code);
                                }),
                                TextEntry::make('created_at')->dateTime(config('app.datetime_format')),
                                TextEntry::make('updated_at')->dateTime(config('app.datetime_format')),

                            ])
                            ->columns([
                                'xl' => 2,
                                '2xl' => 2,
                            ]),
                        \Filament\Infolists\Components\Tabs\Tab::make(__('Changes'))
                            ->icon('heroicon-m-arrow-uturn-down')
                            ->schema(function ($record) {
                                // Filament schema is called multiple times for some reason, so we need to cache the components to avoid performance issues.
                                return static::subscriptionHistoryComponents($record);
                            }),
                    ]),

            ]);
    }

    public static function getWidgets(): array
    {
        return [
            TransactionOverview::class,
        ];
    }

    public static function subscriptionHistoryComponents($record): array
    {
        if (! empty(static::$cachedTransactionHistoryComponents)) {
            return static::$cachedTransactionHistoryComponents;
        }

        $i = 0;
        foreach ($record->versions->reverse() as $version) {
            $versionModel = $version->getModel();

            static::$cachedTransactionHistoryComponents[] = Section::make([
                TextEntry::make('status_'.$i)
                    ->label(__('Status'))
                    ->badge()
                    ->getStateUsing(fn () => $versionModel->status),

                TextEntry::make('provider_status_'.$i)
                    ->label(__('Payment Provider Status'))
                    ->badge()
                    ->getStateUsing(fn () => $versionModel->payment_provider_status),

                TextEntry::make('amount_'.$i)
                    ->label(__('Amount'))
                    ->getStateUsing(function () use ($versionModel) {
                        return money($versionModel->amount, $versionModel->currency->code);
                    }),

            ])->columns(4)->collapsible()->heading(
                date(config('app.datetime_format'), strtotime($version->created_at))
            );

            $i++;
        }

        return static::$cachedTransactionHistoryComponents;
    }

    public static function getNavigationLabel(): string
    {
        return __('Transactions');
    }
}
