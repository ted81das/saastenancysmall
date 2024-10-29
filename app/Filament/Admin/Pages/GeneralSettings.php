<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;

class GeneralSettings extends Page
{
    protected static string $view = 'filament.admin.pages.general-settings';

    protected static ?string $navigationGroup = 'Settings';

    public static function canAccess(): bool
    {
        return auth()->user()->hasPermissionTo('update settings');
    }
}
