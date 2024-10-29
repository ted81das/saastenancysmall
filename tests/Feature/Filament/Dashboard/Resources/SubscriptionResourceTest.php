<?php

namespace Tests\Feature\Filament\Dashboard\Resources;

use App\Constants\TenancyPermissionConstants;
use App\Filament\Dashboard\Resources\SubscriptionResource;
use App\Models\Subscription;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Feature\FeatureTest;

class SubscriptionResourceTest extends FeatureTest
{
    public function test_list(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant, [
            TenancyPermissionConstants::PERMISSION_VIEW_SUBSCRIPTIONS,
        ]);

        $this->actingAs($user);

        $response = $this->get(SubscriptionResource::getUrl('index', [], true, 'dashboard', tenant: $tenant))->assertSuccessful();

        $response->assertStatus(200);
    }

    public function test_list_fails_when_user_has_no_permission(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);

        $this->actingAs($user);
        $this->expectException(HttpException::class);

        $this->get(SubscriptionResource::getUrl('index', [], true, 'dashboard', tenant: $tenant));
    }

    public function test_change_plan(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant, [
            TenancyPermissionConstants::PERMISSION_VIEW_SUBSCRIPTIONS,
        ]);

        $this->actingAs($user);

        // create subscription for this user
        $subscription = Subscription::factory()->for($user)->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
        ]);

        $response = $this->get(SubscriptionResource::getUrl('change-plan', [
            'record' => $subscription->uuid,
        ], true, 'dashboard', tenant: $tenant))->assertSuccessful();

        $response->assertStatus(200);
    }

    public function test_cancel()
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant, [
            TenancyPermissionConstants::PERMISSION_VIEW_SUBSCRIPTIONS,
        ]);

        $this->actingAs($user);

        // create subscription for this user
        $subscription = Subscription::factory()->for($user)->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
        ]);

        $response = $this->get(SubscriptionResource::getUrl('cancel', [
            'record' => $subscription->uuid,
        ], true, 'dashboard', tenant: $tenant))->assertSuccessful();

        $response->assertStatus(200);
    }

    public function test_confirm_cancellation()
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant, [
            TenancyPermissionConstants::PERMISSION_VIEW_SUBSCRIPTIONS,
        ]);

        $this->actingAs($user);

        // create subscription for this user
        $subscription = Subscription::factory()->for($user)->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
        ]);

        $response = $this->get(SubscriptionResource::getUrl('confirm-cancellation', [
            'record' => $subscription->uuid,
        ], true, 'dashboard', tenant: $tenant))->assertSuccessful();

        $response->assertStatus(200);
    }
}
