<?php

namespace App\Filament\Admin\Resources\EmailProviderResource\Pages;

use App\Filament\Admin\Resources\EmailProviderResource;
use Filament\Resources\Pages\Page;

class MailgunSettings extends Page
{
    protected static string $resource = EmailProviderResource::class;

    protected static string $view = 'filament.admin.resources.email-provider-resource.pages.mailgun-settings';

    public function mount(): void
    {
        static::authorizeResourceAccess();
    }
}
