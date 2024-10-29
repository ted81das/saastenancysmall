<?php

namespace App\Filament\Admin\Resources\TenantResource\RelationManagers;

use App\Constants\PlanType;
use App\Constants\SubscriptionStatus;
use App\Filament\Admin\Resources\SubscriptionResource\Pages\ViewSubscription;
use App\Mapper\SubscriptionStatusMapper;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('plan.name')
            ->columns([
                Tables\Columns\TextColumn::make('plan.name'),
                Tables\Columns\TextColumn::make('price')->formatStateUsing(function (string $state, $record) {
                    if ($record->plan->type === PlanType::FLAT_RATE->value) {
                        return money($state, $record->currency->code).' / '.$record->interval->name;
                    } elseif ($record->plan->type === PlanType::SEAT_BASED->value) {
                        return money($state, $record->currency->code).' / '.$record->interval->name.' / '.__('seat');
                    }

                    return money($state, $record->currency->code);
                }),
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
            ->headerActions([

            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn ($record) => ViewSubscription::getUrl(['record' => $record]))
                    ->label(__('View'))
                    ->icon('heroicon-o-eye'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
