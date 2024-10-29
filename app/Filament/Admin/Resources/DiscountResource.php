<?php

namespace App\Filament\Admin\Resources;

use App\Constants\DiscountConstants;
use App\Models\Discount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;

    protected static ?string $navigationGroup = 'Product Management';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // card
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Textarea::make('description')
                        ->maxLength(255),

                    Forms\Components\Radio::make('type')
                        ->required()
                        ->options([
                            DiscountConstants::TYPE_FIXED => __('Fixed amount'),
                            DiscountConstants::TYPE_PERCENTAGE => __('Percentage (of the total price)'),
                        ])
                        ->default('fixed'),

                    Forms\Components\Grid::make()->schema([
                        Forms\Components\TextInput::make('amount')
                            ->helperText(__('If you choose percentage, enter a number between 0 and 100. For example: 90 for 90%. For fixed amount, enter the amount in cents. For example: 1000 for $10.00'))
                            ->integer()
                            ->required(),
                        Forms\Components\DateTimePicker::make('valid_until'),
                    ]),
                    Forms\Components\Select::make('plans')
                        ->multiple()
                        ->relationship('plans', 'name', modifyQueryUsing: function (Builder $query) {
                            return $query->select('plans.id', 'plans.name')->distinct();
                        })
                        ->preload()
                        ->helperText(__('Select the plans that this discount will be applied to. If you leave empty, discount will be applied to all plans.')),
                    Forms\Components\Select::make('oneTimeProducts')
                        ->label(__('One-time purchase products'))
                        ->multiple()
                        ->relationship('oneTimeProducts', 'name', modifyQueryUsing: function (Builder $query) {
                            return $query->select('one_time_products.id', 'one_time_products.name')->distinct();
                        })
                        ->preload()
                        ->helperText(__('Select the one-time products that this discount will be applied to. If you leave empty, discount will be applied to all one-time products.')),
                    //                    Forms\Components\Select::make('action_type')  // TODO: implement this in the future
                    //                        ->options(DiscountConstants::ACTION_TYPES)
                    //                        // change the default value to null
                    //                        ->default(null),
                    Forms\Components\TextInput::make('max_redemptions')
                        ->integer()
                        ->default(-1)
                        ->helperText(__('Enter -1 for unlimited redemptions (total).')),
                    Forms\Components\TextInput::make('max_redemptions_per_user')
                        ->integer()
                        ->default(-1)
                        ->helperText(__('Enter -1 for unlimited redemptions per user.')),
                    Forms\Components\Toggle::make('is_recurring')
                        ->helperText(__('If enabled, this discount will keep being applied to the subscription forever (or until valid if you set maximum valid date).'))
                        ->required(),
                    Forms\Components\Toggle::make('is_active')
                        ->default(true)
                        ->required(),
                    Forms\Components\TextInput::make('duration_in_months')
                        ->integer()
                        ->helperText(__('This allows you define how many months the discount should apply. Only works with payment providers that support this feature. (like Stripe or Lemon Squeezy)'))
                        ->default(null),
                    Forms\Components\TextInput::make('maximum_recurring_intervals')
                        ->integer()
                        ->helperText(__('Amount of subscription billing periods that this discount recurs for. Only works with payment providers that support this feature. (like Paddle)'))
                        ->default(null),

                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('amount')->formatStateUsing(function (string $state, $record) {
                    if ($record->type === DiscountConstants::TYPE_PERCENTAGE) {
                        return $state.'%';
                    }

                    return intval($state) / 100;
                }),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('redemptions'),
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
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Admin\Resources\DiscountResource\RelationManagers\CodesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Admin\Resources\DiscountResource\Pages\ListDiscounts::route('/'),
            'create' => \App\Filament\Admin\Resources\DiscountResource\Pages\CreateDiscount::route('/create'),
            'edit' => \App\Filament\Admin\Resources\DiscountResource\Pages\EditDiscount::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('Discounts');
    }
}
