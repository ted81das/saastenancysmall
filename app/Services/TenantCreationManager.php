<?php

namespace App\Services;

use App\Constants\SubscriptionStatus;
use App\Constants\TenancyPermissionConstants;
use App\Constants\TenantConstants;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;

class TenantCreationManager
{
    public function __construct(
        private TenantPermissionManager $tenantPermissionManager,
    ) {

    }

    public function findUserTenantsForNewOrder(?User $user)
    {
        if ($user === null) {
            return collect();
        }

        return $this->tenantPermissionManager->filterTenantsWhereUserHasPermission(
            $user->tenants()->get(),
            TenancyPermissionConstants::PERMISSION_CREATE_ORDERS
        );
    }

    public function findUserTenantForNewOrderByUuid(User $user, ?string $tenantUuid): ?Tenant
    {
        if ($tenantUuid === null) {
            return null;
        }

        return $this->tenantPermissionManager->filterTenantsWhereUserHasPermission(
            $user->tenants()->where('uuid', $tenantUuid)->get(),
            TenancyPermissionConstants::PERMISSION_CREATE_ORDERS
        )->first();
    }

    public function findUserTenantsForNewSubscription(?User $user)
    {
        if ($user === null) {
            return collect();
        }

        // where doesn't have any subscriptions with status other than New
        return $this->tenantPermissionManager->filterTenantsWhereUserHasPermission(
            $user->tenants()->whereDoesntHave('subscriptions', function ($query) {
                $query->where('status', '!=', SubscriptionStatus::NEW->value);
            })->get(),
            TenancyPermissionConstants::PERMISSION_CREATE_SUBSCRIPTIONS
        );
    }

    public function findUserTenantForNewSubscriptionByUuid(User $user, ?string $tenantUuid): ?Tenant
    {
        if ($tenantUuid === null) {
            return null;
        }

        return $this->tenantPermissionManager->filterTenantsWhereUserHasPermission(
            $user->tenants()->whereDoesntHave('subscriptions', function ($query) {
                $query->where('status', '!=', SubscriptionStatus::NEW->value);
            })->where('uuid', $tenantUuid)->get(),
            TenancyPermissionConstants::PERMISSION_CREATE_SUBSCRIPTIONS
        )->first();
    }

    public function createTenant(User $user): Tenant
    {
        // add an enumeration to the name to avoid name conflicts

        $latestUserTenant = $user->tenants()->latest()->first();

        $number = 1;
        if ($latestUserTenant) {
            $parts = explode('#', $latestUserTenant->name);
            if (count($parts) > 1) {
                $number = $parts[count($parts) - 1];
                $number = (int) $number + 1;
            }
        }

        $name = $user->name.' '.TenantConstants::getAlias();

        $name .= ' #'.$number;

        $tenant = Tenant::create([
            'name' => $name,
            'uuid' => (string) Str::uuid(),
            'is_name_auto_generated' => true,
            'created_by' => $user->id,
        ]);

        $tenant->users()->attach($user);

        $this->tenantPermissionManager->assignTenantUserRole($tenant, $user, TenancyPermissionConstants::TENANT_CREATOR_ROLE);

        return $tenant;
    }

    public function createTenantForFreePlanUser(User $user)
    {
        if ($user->tenants->count() == 0) {
            $this->createTenant($user);
        }
    }
}
