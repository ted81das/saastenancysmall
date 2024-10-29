<?php

namespace App\Filament\Admin\Resources;

use App\Constants\RoadmapItemStatus;
use App\Constants\RoadmapItemType;
use App\Filament\Admin\Resources\RoadmapItemResource\Pages;
use App\Filament\Admin\Resources\RoadmapItemResource\RelationManagers;
use App\Mapper\RoadmapMapper;
use App\Models\RoadmapItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class RoadmapItemResource extends Resource
{
    protected static ?string $model = RoadmapItem::class;

    protected static ?string $navigationGroup = 'Roadmap';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('slug')
                        ->label(__('Slug'))
                        ->dehydrateStateUsing(function ($state, \Filament\Forms\Get $get) {
                            if (empty($state)) {
                                // add a random string if there is a roadmap item with the same slug
                                $state = Str::slug($get('title'));
                                if (RoadmapItem::where('slug', $state)->exists()) {
                                    $state .= '-'.Str::random(5);
                                }

                                return Str::slug($state);
                            }

                            return $state;
                        })
                        ->maxLength(255),

                    Forms\Components\Textarea::make('description')
                        ->rows(5)
                        ->columnSpanFull(),
                    Forms\Components\Select::make('status')
                        ->options(function () {
                            return collect(RoadmapItemStatus::cases())->mapWithKeys(function ($status) {
                                return [$status->value => RoadmapMapper::mapStatusForDisplay($status)];
                            });
                        })
                        ->required()
                        ->default(RoadmapItemStatus::APPROVED->value),
                    Forms\Components\Select::make('type')
                        ->options(function () {
                            return collect(RoadmapItemType::cases())->mapWithKeys(function ($type) {
                                return [$type->value => RoadmapMapper::mapTypeForDisplay($type)];
                            });
                        })
                        ->required()
                        ->default(RoadmapItemType::FEATURE->value),
                    Forms\Components\TextInput::make('upvotes')
                        ->label(__('Upvotes'))
                        ->required()
                        ->numeric()
                        ->default(1),
                    Forms\Components\Select::make('user_id')
                        ->label(__('User'))
                        ->lazy()
                        ->searchable()
                        ->options(fn () => \App\Models\User::pluck('name', 'id'))
                        ->default(fn () => auth()->user()->id)
                        ->required(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\SelectColumn::make('status')
                    ->options(function () {
                        return collect(RoadmapItemStatus::cases())->mapWithKeys(function ($status) {
                            return [$status->value => RoadmapMapper::mapStatusForDisplay($status)];
                        });
                    })
                    ->rules(['required'])
                    ->searchable(),
                Tables\Columns\SelectColumn::make('type')
                    ->options(function () {
                        return collect(RoadmapItemType::cases())->mapWithKeys(function ($type) {
                            return [$type->value => RoadmapMapper::mapTypeForDisplay($type)];
                        });
                    })
                    ->rules(['required'])
                    ->searchable(),
                Tables\Columns\TextColumn::make('upvotes')
                    ->default(1)
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_id')
                    ->label(__('User'))
                    ->formatStateUsing(function ($state) {
                        return \App\Models\User::find($state)->name;
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('upvotes', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UpvotesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoadmapItems::route('/'),
            'create' => Pages\CreateRoadmapItem::route('/create'),
            'edit' => Pages\EditRoadmapItem::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationLabel(): string
    {
        return __('Roadmap Items');
    }
}
