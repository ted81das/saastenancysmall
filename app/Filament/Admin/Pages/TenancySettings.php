<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;

class TenancySettings extends Page
{
    protected static string $view = 'filament.admin.pages.tenancy-settings';

    protected static ?string $navigationGroup = 'Settings';

    public static function canAccess(): bool
    {
        return auth()->user()->hasPermissionTo('update settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Tenancy');
    }
}
