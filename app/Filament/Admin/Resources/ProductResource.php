<?php

namespace App\Filament\Admin\Resources;

use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationGroup = 'Product Management';

    protected static ?int $navigationSort = 1;

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
                                if (Product::where('slug', $state)->exists()) {
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
                    Forms\Components\Toggle::make('is_popular')
                        ->label(__('Popular product'))
                        ->helperText(__('Mark this product as popular. This will be used to highlight this product in the pricing page.')),
                    Forms\Components\Toggle::make('is_default')
                        ->label(__('Is default product'))
                        ->validationAttribute(__('default product'))
                        ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule) {
                            return $rule->where('is_default', true);
                        })
                        ->default(false)
                        ->helperText(__('A default product is a kind of a hidden product that allows you to set the features (and metadata) for users that have no active plan. Add a default product if you want to offer a free tier to your users. You can only have 1 default product and it cannot have any plans.')),
                    Forms\Components\KeyValue::make('metadata')
                        ->helperText(__('Add any additional data to this product. You can use this to store product features that could later be retrieved to serve your users.'))
                        ->keyLabel(__('Property name'))
                        ->valueLabel(__('Property value')),
                    Forms\Components\Repeater::make('features')
                        ->helperText(__('Add features that this plan offers. These will be displayed on the pricing page and on the checkout page.'))
                        ->schema([
                            Forms\Components\TextInput::make('feature')->required(),
                        ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading(__('A product is bundle of features that you offer to your customers.'))
            ->description(__('If you want to provide a Starter, Pro and Premium offerings to your customers, create a product for each of them.'))
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->searchable()->sortable(),
                Tables\Columns\IconColumn::make('is_popular')->label(__('Popular'))->boolean(),
                Tables\Columns\IconColumn::make('is_default')->label(__('Default'))->boolean(),
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

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Admin\Resources\ProductResource\Pages\ListProducts::route('/'),
            'create' => \App\Filament\Admin\Resources\ProductResource\Pages\CreateProduct::route('/create'),
            'edit' => \App\Filament\Admin\Resources\ProductResource\Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Subscription Product');
    }

    public static function getNavigationLabel(): string
    {
        return __('Subscription Products');
    }
}
