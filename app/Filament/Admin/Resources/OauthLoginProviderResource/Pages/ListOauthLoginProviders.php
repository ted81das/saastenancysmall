<?php

namespace App\Filament\Admin\Resources\OauthLoginProviderResource\Pages;

use App\Filament\Admin\Resources\OauthLoginProviderResource;
use App\Filament\ListDefaults;
use Filament\Resources\Pages\ListRecords;

class ListOauthLoginProviders extends ListRecords
{
    use ListDefaults;

    protected static string $resource = OauthLoginProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
