<?php

namespace App\Filament\Admin\Resources\OauthLoginProviderResource\Pages;

use App\Filament\Admin\Resources\OauthLoginProviderResource;
use App\Filament\CrudDefaults;
use App\Models\OauthLoginProvider;
use Filament\Resources\Pages\EditRecord;

class EditOauthLoginProvider extends EditRecord
{
    use CrudDefaults;

    protected static string $resource = OauthLoginProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
            \Filament\Actions\Action::make('edit-credentials')
                ->label(__('Edit Credentials'))
                ->color('primary')
                ->icon('heroicon-o-rocket-launch')
                ->url(fn (OauthLoginProvider $record): string => \App\Filament\Admin\Resources\OauthLoginProviderResource::getUrl(
                    $record->provider_name.'-settings'
                )),
        ];
    }
}
