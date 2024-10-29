<?php

namespace App\Filament\Dashboard\Resources;

use App\Constants\DiscountConstants;
use App\Constants\OrderStatus;
use App\Filament\Dashboard\Resources\OrderResource\Pages;
use App\Mapper\OrderStatusMapper;
use App\Models\Order;
use App\Services\ConfigManager;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                Tables\Columns\TextColumn::make('total_amount_after_discount')->formatStateUsing(function (string $state, $record) {
                    if ($record->transactions()->count() > 0) {
                        $transaction = $record->transactions()->first();

                        return money($transaction->amount, $transaction->currency->code);
                    }

                    return money($state, $record->currency->code);
                })->label(__('Total Amount')),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        OrderStatus::SUCCESS->value => 'success',
                    ])
                    ->formatStateUsing(
                        function (string $state, $record, OrderStatusMapper $mapper) {
                            return $mapper->mapForDisplay($state);
                        })
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')->label(__('Updated At'))
                    ->dateTime(config('app.datetime_format'))
                    ->searchable()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([

            ])->defaultSort('updated_at', 'desc');
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
                                Section::make(__('Order Details'))
                                    ->description(__('View details about this order.'))
                                    ->schema([
                                        TextEntry::make('uuid')->copyable(),
                                        TextEntry::make('total_amount')->formatStateUsing(function (string $state, $record) {
                                            if ($record->transactions()->count() > 0) {
                                                $transaction = $record->transactions()->first();

                                                return money($transaction->amount, $transaction->currency->code);
                                            }

                                            return money($state, $record->currency->code);
                                        }),
                                        TextEntry::make('total_discount_amount')->formatStateUsing(function (string $state, $record) {
                                            if ($record->transactions()->count() > 0) {
                                                $transaction = $record->transactions()->first();

                                                return money($transaction->total_discount, $transaction->currency->code);
                                            }

                                            return money($state, $record->currency->code);
                                        })->visible(fn (Order $record): bool => $record->discounts()->count() > 0),
                                        TextEntry::make('total_tax_amount')->getStateUsing(function ($record) {
                                            if ($record->transactions()->count() > 0) {
                                                $transaction = $record->transactions()->first();

                                                return money($transaction->total_tax, $transaction->currency->code);
                                            }

                                            return money(0, $record->currency->code);
                                        }),
                                        TextEntry::make('status')
                                            ->formatStateUsing(fn (string $state, OrderStatusMapper $mapper): string => $mapper->mapForDisplay($state))
                                            ->badge(),
                                        TextEntry::make('discounts.amount')
                                            ->hidden(fn (Order $record): bool => $record->discounts()->count() === 0)
                                            ->formatStateUsing(function (string $state, $record) {
                                                if ($record->discounts[0]->type === DiscountConstants::TYPE_PERCENTAGE) {
                                                    return $state.'%';
                                                }

                                                return money($state, $record->discounts[0]->code);
                                            })->label(__('Discount Amount')),
                                        TextEntry::make('updated_at')->dateTime(config('app.datetime_format')),
                                    ])->columns(3),
                                Section::make(__('Order Items'))
                                    ->description(__('View details about order items.'))
                                    ->schema(
                                        function ($record) {
                                            // Filament schema is called multiple times for some reason, so we need to cache the components to avoid performance issues.
                                            return static::orderItems($record);
                                        },
                                    ),
                            ]),
                    ]),

            ]);

    }

    public static function orderItems(Order $order): array
    {
        $result = [];

        $i = 0;
        foreach ($order->items()->get() as $item) {
            $section = Section::make(function () use ($item) {
                return $item->oneTimeProduct->name;
            })
                ->schema([
                    TextEntry::make('items.quantity_'.$i)->getStateUsing(fn () => $item->quantity)->label(__('Quantity')),
                    TextEntry::make('items.price_per_unit_'.$i)->getStateUsing(fn () => money($item->price_per_unit, $order->currency->code))->label(__('Price Per Unit')),
                    TextEntry::make('items.price_per_unit_after_discount_'.$i)->getStateUsing(fn () => money($item->price_per_unit_after_discount, $order->currency->code))->label(__('Price Per Unit After Discount')),
                    TextEntry::make('items.discount_per_unit_'.$i)->getStateUsing(fn () => money($item->discount_per_unit, $order->currency->code))->label(__('Discount Per Unit')),
                ])
                ->columns(4);

            $result[] = $section;
            $i++;
        }

        return $result;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('tenant_id', Filament::getTenant()->id);
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
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function isDiscovered(): bool
    {
        return app()->make(ConfigManager::class)->get('app.customer_dashboard.show_orders', true);
    }
}
