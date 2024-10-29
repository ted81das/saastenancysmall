<?php

namespace App\Filament\Admin\Resources\PlanResource\RelationManagers;

use App\Constants\PaymentProviderConstants;
use App\Models\PaymentProvider;
use App\Services\PaymentProviders\LemonSqueezy\LemonSqueezyProductValidator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class PaymentProviderDataRelationManager extends RelationManager
{
    protected static string $relationship = 'paymentProviderData';

    protected static ?string $recordTitleAttribute = 'id';

    public static function getModelLabel(): ?string
    {
        return __('Payment Provider Data');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('payment_provider_id')->label('Payment Provider')
                    ->options(
                        \App\Models\PaymentProvider::all()
                            ->mapWithKeys(function ($paymentProvider) {
                                return [$paymentProvider->id => $paymentProvider->name];
                            })
                            ->toArray()
                    )
                    ->default(\App\Models\PaymentProvider::where('slug', PaymentProviderConstants::LEMON_SQUEEZY_SLUG)?->first()?->id ?? null)
                    ->live()
                    ->unique(modifyRuleUsing: function ($rule, \Filament\Forms\Get $get, RelationManager $livewire) {
                        return $rule->where('plan_id', $livewire->ownerRecord->id)->ignore($get('id'));
                    })
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('payment_provider_product_id')
                    ->label('Payment Provider Product/Variant ID')
                    ->helperText('For Lemon Squeezy, this should be equal to the variant ID.')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('submit')
                        ->label(__('Validate Product (Lemon Squeezy)'))
                        ->color('success')
                        ->outlined()
                        ->disabled(fn ($get) => $get('payment_provider_id') != PaymentProvider::where('slug', PaymentProviderConstants::LEMON_SQUEEZY_SLUG)?->first()?->id)
                        ->action(function (LemonSqueezyProductValidator $validator, $get) {
                            if ($get('payment_provider_id') != PaymentProvider::where('slug', PaymentProviderConstants::LEMON_SQUEEZY_SLUG)?->first()?->id) {
                                Notification::make()
                                    ->danger()
                                    ->title(__('Invalid Payment Provider'))
                                    ->body(__('The selected payment provider is not Lemon Squeezy.'))
                                    ->persistent()
                                    ->send();

                                return;
                            }

                            $variantId = $get('payment_provider_product_id');

                            try {
                                $validator->validatePlan($variantId, $this->ownerRecord);
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->danger()
                                    ->title(__('Problem validating product'))
                                    ->body(__($e->getMessage()))
                                    ->persistent()
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->success()
                                ->title(__('Product found'))
                                ->body(__('The product with the variant ID :variantId was found and is matching your plan product details.', ['variantId' => $variantId]))
                                ->persistent()
                                ->send();
                        }),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->description(new HtmlString('⚠️ Advanced settings, these records are created automatically when a plan is created. You <b>SHOULD NOT</b> need to create or edit these records manually unless you use "Lemon Squeezy" as your payment provider because it does not support plan creation via the API.'))
            ->recordTitleAttribute('Payment Provider Product/Variant ID')
            ->columns([
                Tables\Columns\TextColumn::make('payment_provider_id')->label('Payment Provider')->formatStateUsing(function ($record) {
                    return $record->paymentProvider->name;
                }),
                Tables\Columns\TextColumn::make('payment_provider_product_id')->label('Payment Provider Product/Variant ID'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
