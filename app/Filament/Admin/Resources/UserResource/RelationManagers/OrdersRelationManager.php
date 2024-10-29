<?php

namespace App\Filament\Admin\Resources\UserResource\RelationManagers;

use App\Constants\OrderStatus;
use App\Filament\Admin\Resources\OrderResource;
use App\Mapper\OrderStatusMapper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    public function form(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user_id')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label(__('Id'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => OrderStatus::SUCCESS->value,
                    ])
                    ->formatStateUsing(
                        function (string $state, $record, OrderStatusMapper $mapper) {
                            return $mapper->mapForDisplay($state);
                        }),
                Tables\Columns\TextColumn::make('total_amount')->formatStateUsing(function (string $state, $record) {
                    return money($state, $record->currency->code);
                }),
                Tables\Columns\TextColumn::make('total_amount_after_discount')->formatStateUsing(function (string $state, $record) {
                    return money($state, $record->currency->code);
                }),
                Tables\Columns\TextColumn::make('total_discount_amount')->formatStateUsing(function (string $state, $record) {
                    return money($state, $record->currency->code);
                }),
                Tables\Columns\TextColumn::make('payment_provider_id')
                    ->formatStateUsing(function (string $state, $record) {
                        return $record->paymentProvider->name;
                    })
                    ->label(__('Payment Provider'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')->label(__('Updated At'))
                    ->dateTime(config('app.datetime_format'))
                    ->searchable()->sortable(),

            ])
            ->filters([
                //
            ])
            ->headerActions([

            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn ($record) => OrderResource\Pages\ViewOrder::getUrl(['record' => $record]))
                    ->label(__('View'))
                    ->icon('heroicon-o-eye'),
            ])
            ->bulkActions([

            ]);
    }
}
