<?php

namespace App\Filament\Admin\Resources\RoadmapItemResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UpvotesRelationManager extends RelationManager
{
    protected static string $relationship = 'upvotes';

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
                Tables\Columns\TextColumn::make('user_id')
                    ->label(__('User'))
                    ->formatStateUsing(function ($state) {
                        return \App\Models\User::find($state)->name;
                    }),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label(__('IP Address')),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(config('app.datetime_format'))
                    ->label(__('Created At')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->actions([
            ])
            ->bulkActions([

            ]);
    }
}
