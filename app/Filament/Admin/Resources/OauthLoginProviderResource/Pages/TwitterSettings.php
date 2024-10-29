<?php

namespace App\Filament\Admin\Resources\OauthLoginProviderResource\Pages;

use App\Filament\Admin\Resources\OauthLoginProviderResource;
use Filament\Resources\Pages\Page;

class TwitterSettings extends Page
{
    protected static string $resource = OauthLoginProviderResource::class;

    protected static string $view = 'filament.admin.resources.oauth-login-provider-resource.pages.twitter-settings';

    public function mount(): void
    {
        static::authorizeResourceAccess();
    }
}
