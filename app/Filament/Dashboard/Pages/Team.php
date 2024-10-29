<?php

namespace App\Filament\Dashboard\Pages;

use App\Constants\TenancyPermissionConstants;
use App\Services\TenantPermissionManager;
use Filament\Facades\Filament;
use Filament\Pages\Page;

class Team extends Page
{
    protected static ?string $navigationGroup = 'Team';

    protected static string $view = 'filament.dashboard.pages.team';

    public static function getNavigationLabel(): string
    {
        return __('Team Members');
    }

    public static function canAccess(): bool
    {
        $tenantPermissionManager = app(TenantPermissionManager::class); // a bit ugly, but this is the Filament way :/
        return config('app.allow_tenant_invitations', false) && $tenantPermissionManager->tenantUserHasPermissionTo(
            Filament::getTenant(),
            auth()->user(),
            TenancyPermissionConstants::PERMISSION_MANAGE_TEAM
        );
    }
}
