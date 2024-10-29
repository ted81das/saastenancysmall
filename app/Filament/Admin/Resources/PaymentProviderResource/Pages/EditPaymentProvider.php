<?php

namespace App\Filament\Admin\Resources\PaymentProviderResource\Pages;

use App\Filament\Admin\Resources\PaymentProviderResource;
use App\Models\PaymentProvider;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaymentProvider extends EditRecord
{
    protected static string $resource = PaymentProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            \Filament\Actions\Action::make('edit-credentials')
                ->label(__('Edit Credentials'))
                ->color('primary')
                ->icon('heroicon-o-rocket-launch')
                ->url(fn (PaymentProvider $record): string => \App\Filament\Admin\Resources\PaymentProviderResource::getUrl(
                    $record->slug.'-settings'
                )),
        ];
    }
}
