<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OneTimeProductResource\Pages;
use App\Filament\Admin\Resources\OneTimeProductResource\RelationManagers;
use App\Models\OneTimeProduct;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OneTimeProductResource extends Resource
{
    protected static ?string $model = OneTimeProduct::class;

    protected static ?string $navigationGroup = 'Product Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('slug')
                        ->dehydrateStateUsing(function ($state, \Filament\Forms\Get $get) {
                            if (empty($state)) {
                                // add a random string if there is a product with the same slug
                                $state = Str::slug($get('name'));
                                if (OneTimeProduct::where('slug', $state)->exists()) {
                                    $state .= '-'.Str::random(5);
                                }

                                return Str::slug($state);
                            }

                            return $state;
                        })
                        ->helperText(__('Leave empty to generate slug automatically from product name.'))
                        ->maxLength(255)
                        ->rules(['alpha_dash'])
                        ->unique(ignoreRecord: true)
                        ->disabledOn('edit'),
                    Forms\Components\Textarea::make('description')
                        ->helperText(__('One line description of the product.')),
                    //                    Forms\Components\TextInput::make('max_quantity')  // todo: enable this later
                    //                        ->type('number')
                    //                        ->required()
                    //                        ->default(1)
                    //                        ->minValue(1)
                    //                        ->helperText(__('The maximum quantity of this product that can be purchased at once. If set to 1, customers will not be able to edit the quantity on the checkout page.')),
                    Forms\Components\Toggle::make('is_active')
                        ->helperText(__('If the product is not active, your customers will not be able to purchase it.'))
                        ->default(true)
                        ->label(__('Active')),
                    Forms\Components\KeyValue::make('metadata')
                        ->helperText(__('Add any additional data to this product. You can use this to store product features that could later be retrieved to serve your users.'))
                        ->keyLabel(__('Property name'))
                        ->valueLabel(__('Property value')),
                    Forms\Components\Repeater::make('features')
                        ->helperText(__('Add features that this product offers. These will be displayed on the checkout page.'))
                        ->schema([
                            Forms\Components\TextInput::make('feature')->required(),
                        ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description(__('A one-time purchase product is a non-recurring product that is purchased once for a certain price.'))
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(config('app.datetime_format')),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOneTimeProducts::route('/'),
            'create' => Pages\CreateOneTimeProduct::route('/create'),
            'edit' => Pages\EditOneTimeProduct::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('One-time Purchase Product');
    }

    public static function getNavigationLabel(): string
    {
        return __('One-time Purchase Products');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PricesRelationManager::class,
            RelationManagers\PaymentProviderDataRelationManager::class,
        ];
    }

    public static function canDelete(Model $record): bool
    {
        return $record->isDeletable();
    }
}
