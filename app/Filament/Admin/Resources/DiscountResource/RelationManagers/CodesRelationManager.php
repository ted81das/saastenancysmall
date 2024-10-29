<?php

namespace App\Filament\Admin\Resources\DiscountResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CodesRelationManager extends RelationManager
{
    protected static string $relationship = 'codes';

    protected static ?string $recordTitleAttribute = 'code';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('code')
                        ->helperText(__('The code that will be used to redeem the discount.'))
                        ->required()
                        ->unique()
                        ->maxLength(255),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code'),
                Tables\Columns\TextColumn::make('redemptions_count')
                    ->counts('redemptions'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\Action::make(__('add_bulk_codes'))
                    ->label(__('New Bulk Codes'))
                    ->color('gray')
                    ->button()
                    ->form([
                        Forms\Components\TextInput::make('prefix')
                            ->helperText(__('The prefix will be added to the beginning of each code.'))
                            ->label(__('Prefix')),
                        Forms\Components\TextInput::make('count')
                            ->label(__('Count'))
                            ->helperText(__('The number of codes to generate.'))
                            ->type('number')
                            ->integer()
                            ->minValue(1)
                            ->default(1)
                            ->required(),
                    ])
                    ->action(function (array $data, $livewire) {
                        $record = $livewire->getOwnerRecord();
                        $prefix = $data['prefix'] ?? '';
                        $count = $data['count'] ?? 1;

                        $codes = collect(range(1, $count))
                            ->map(fn () => $prefix.'-'.strtoupper(Str::random(8)))
                            ->map(fn ($code) => ['code' => $code]);

                        $record->codes()->createMany($codes);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
