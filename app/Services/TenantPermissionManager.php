<?php

namespace App\Services;

use App\Constants\TenancyPermissionConstants;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class TenantPermissionManager
{
    private static $permissionCache = [];

    public function tenantUserHasPermissionTo(?Tenant $tenant, User $user, string $permission): bool
    {
        if ($tenant === null) {
            return false;
        }

        // we need some kind of cache because filament calls this method multiple times
        // filament tenant switcher is inefficient and tried to build the navigation for all tenants, only to display the menu for the current tenant
        // I'm sure this will be solved in the future, but for now, we need to cache the permissions to reduce the number of queries to the database
        // this cache gets all the permissions of a user for a tenant and stores them in an array for quick access
        // you need to find another way to cache this (or avoid caching altogether) if you want to use FrankenPhp to avoid memory leaks
        // if you decide to avoid caching you can replace the content of this function with:
        // return $user->tenants()->where('tenant_id', $tenant->id)?->first()?->pivot?->hasPermissionTo($permission) ?? false;

        if (isset(self::$permissionCache[$tenant->id][$user->id]) && is_bool(self::$permissionCache[$tenant->id][$user->id])) { // if the user has no permissions at all
            return self::$permissionCache[$tenant->id][$user->id];
        }

        if (isset(self::$permissionCache[$tenant->id][$user->id][$permission])) {
            return self::$permissionCache[$tenant->id][$user->id][$permission];
        }

        $allPermissions = $user->tenants()->where('tenant_id', $tenant->id)?->first()?->pivot?->getAllPermissions();

        if ($allPermissions->count() === 0) {
            self::$permissionCache[$tenant->id][$user->id] = false;

            return false;
        }

        foreach ($allPermissions as $onePermission) {
            self::$permissionCache[$tenant->id][$user->id][$onePermission->name] = true;
        }

        return self::$permissionCache[$tenant->id][$user->id][$permission] ?? false;
    }

    public function filterTenantsWhereUserHasPermission(Collection $tenants, string $permission)
    {
        return $tenants->filter(function ($tenant) use ($permission) {
            return $tenant->pivot->hasPermissionTo($permission);
        });
    }

    public function getTenantUserRoles(Tenant $tenant, User $user): array
    {
        return $user->tenants()->where('tenant_id', $tenant->id)?->first()?->pivot?->getRoleNames()->toArray() ?? [];
    }

    public function assignTenantUserRole(Tenant $tenant, User $user, string $role): void
    {
        $this->removeAllTenantUserRoles($tenant, $user);
        $user->tenants()->where('tenant_id', $tenant->id)->first()->pivot->assignRole($role);
    }

    public function removeAllTenantUserRoles(Tenant $tenant, User $user): void
    {
        $user->tenants()->where('tenant_id', $tenant->id)->first()->pivot->syncRoles([]);
    }

    public function getAllAvailableTenantRolesForDisplay()
    {
        $roles = Role::where('name', 'like', TenancyPermissionConstants::TENANCY_ROLE_PREFIX.'%')->get()->pluck('name');

        $result = [];
        foreach ($roles as $role) {
            $result[$role] = Str::of($role)->replace(TenancyPermissionConstants::TENANCY_ROLE_PREFIX, '')->replace('-', ' ')->title();
        }

        return $result;
    }
}
