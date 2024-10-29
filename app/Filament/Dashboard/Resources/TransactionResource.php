<?php

namespace App\Filament\Dashboard\Resources;

use App\Constants\TransactionStatus;
use App\Filament\Dashboard\Resources\OrderResource\Pages\ViewOrder;
use App\Filament\Dashboard\Resources\SubscriptionResource\Pages\ViewSubscription;
use App\Filament\Dashboard\Resources\TransactionResource\Pages;
use App\Mapper\TransactionStatusMapper;
use App\Models\Transaction;
use App\Services\AddressManager;
use App\Services\ConfigManager;
use App\Services\InvoiceManager;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('amount')->formatStateUsing(function (string $state, $record) {
                    return money($state, $record->currency->code);
                }),
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(fn (string $state, TransactionStatusMapper $mapper): string => $mapper->mapForDisplay($state)),
                Tables\Columns\TextColumn::make('owner')
                    ->label(__('Owner'))
                    ->getStateUsing(fn (Transaction $record) => $record->subscription_id !== null ? ($record->subscription->plan?->name ?? '-') : ($record->order_id !== null ? __('View Order') : '-'))
                    ->url(fn (Transaction $record) => $record->subscription_id !== null ? ViewSubscription::getUrl(['record' => $record->subscription]) : ($record->order_id !== null ? ViewOrder::getUrl(['record' => $record->order]) : '-')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Date'))
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('see-invoice')
                    ->label(__('See Invoice'))
                    ->icon('heroicon-o-document')
                    ->visible(fn (Transaction $record, InvoiceManager $invoiceManager): bool => $invoiceManager->canGenerateInvoices($record))
                    ->modalDescription(function (AddressManager $addressManager) {
                        if (! $addressManager->userHasAddressInfo(auth()->user())) {
                            return __('Your address information is not complete. It is recommended to complete your address information before generating an invoice. Are you sure you want to proceed?');
                        }

                        return null;
                    })
                    ->modalCancelAction(
                        Action::make('complete-address-information')
                            ->label(__('Complete Address Info'))
                            ->url(route('filament.dashboard.pages.my-profile', ['tenant' => Filament::getTenant()]))
                    )
                    ->modalSubmitActionLabel(__('Proceed anyway'))
                    ->action(function (Transaction $record) {
                        return redirect()->route('invoice.generate', ['transactionUuid' => $record->uuid]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListTransactions::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function canUpdate(Model $record): bool
    {
        return false;
    }

    public static function canUpdateAny(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('tenant_id', Filament::getTenant()->id)->where('amount', '>', 0)->where('status', '!=', TransactionStatus::NOT_STARTED->value);
    }

    public static function getModelLabel(): string
    {
        return __('Payments');
    }

    public static function isDiscovered(): bool
    {
        return app()->make(ConfigManager::class)->get('app.customer_dashboard.show_transactions', true);
    }
}
