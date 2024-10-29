<?php

namespace Tests\Feature\Services;

use App\Constants\TenancyPermissionConstants;
use App\Services\TenantCreationManager;
use App\Services\TenantPermissionManager;
use Tests\Feature\FeatureTest;

class TenantCreationManagerTest extends FeatureTest
{
    public function test_create_tenant(): void
    {
        $user = $this->createUser();

        $tenantPermissionManager = \Mockery::mock(TenantPermissionManager::class);
        $tenantPermissionManager->shouldReceive('assignTenantUserRole')
            ->once()
            ->with(\Mockery::any(), $user, TenancyPermissionConstants::TENANT_CREATOR_ROLE);

        $tenantCreationManager = new TenantCreationManager($tenantPermissionManager);

        $tenant = $tenantCreationManager->createTenant($user);

        $this->assertEquals(1, $user->tenants()->count());
    }
}
