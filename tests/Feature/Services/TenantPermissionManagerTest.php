<?php

namespace Tests\Feature\Services;

use App\Constants\TenancyPermissionConstants;
use App\Services\TenantPermissionManager;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Feature\FeatureTest;

class TenantPermissionManagerTest extends FeatureTest
{
    public function test_tenant_user_has_permission(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant, [TenancyPermissionConstants::PERMISSION_UPDATE_SUBSCRIPTIONS]);

        $this->actingAs($user);

        $tenantPermissionManager = new TenantPermissionManager();

        $this->assertTrue($tenantPermissionManager->tenantUserHasPermissionTo($tenant, $user, TenancyPermissionConstants::PERMISSION_UPDATE_SUBSCRIPTIONS));
        $this->assertFalse($tenantPermissionManager->tenantUserHasPermissionTo($tenant, $user, TenancyPermissionConstants::PERMISSION_VIEW_SUBSCRIPTIONS));
    }

    public function test_tenant_assign_role(): void
    {
        $role = Role::findOrCreate('tenancy: test role');
        $permission = Permission::findOrCreate('tenancy: test permission');
        $role->givePermissionTo([$permission]);

        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);

        $tenantPermissionManager = new TenantPermissionManager();

        $tenantPermissionManager->assignTenantUserRole($tenant, $user, $role->name);

        $tenantRoles = $tenantPermissionManager->getTenantUserRoles($tenant, $user);

        $this->assertContains($role->name, $tenantRoles);
        $this->assertTrue($tenantPermissionManager->tenantUserHasPermissionTo($tenant, $user, 'tenancy: test permission'));
    }

    public function test_tenant_remove_all_role(): void
    {
        $role = Role::findOrCreate('tenancy: test role');
        $permission = Permission::findOrCreate('tenancy: test permission');
        $role->givePermissionTo([$permission]);

        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);

        $tenantPermissionManager = new TenantPermissionManager();

        $tenantPermissionManager->assignTenantUserRole($tenant, $user, $role->name);

        $tenantPermissionManager->removeAllTenantUserRoles($tenant, $user);

        $tenantRoles = $tenantPermissionManager->getTenantUserRoles($tenant, $user);

        $this->assertEmpty($tenantRoles);
        $this->assertFalse($tenantPermissionManager->tenantUserHasPermissionTo($tenant, $user, 'tenancy: test permission'));
    }
}
