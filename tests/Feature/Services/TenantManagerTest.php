<?php

namespace Tests\Feature\Services;

use App\Constants\PlanType;
use App\Constants\SubscriptionStatus;
use App\Constants\TenancyPermissionConstants;
use App\Events\Tenant\UserJoinedTenant;
use App\Events\Tenant\UserRemovedFromTenant;
use App\Models\Currency;
use App\Models\Invitation;
use App\Models\PaymentProvider;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Services\PaymentProviders\PaymentManager;
use App\Services\PaymentProviders\PaymentProviderInterface;
use App\Services\TenantManager;
use App\Services\TenantPermissionManager;
use App\Services\TenantSubscriptionManager;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;
use Tests\Feature\FeatureTest;

class TenantManagerTest extends FeatureTest
{
    public function test_accept_invitation_seat_based_plan()
    {
        $tenant = $this->createTenant();
        $inviter = $this->createUser($tenant);

        $plan = Plan::factory()->create([
            'slug' => 'plan-slug-'.uniqid(),
            'is_active' => true,
            'type' => PlanType::SEAT_BASED->value,
        ]);

        PlanPrice::create([
            'plan_id' => $plan->id,
            'currency_id' => Currency::where('code', 'USD')->first()->id,
            'price' => 100,
        ]);

        /** @var PaymentProviderInterface|MockInterface $paymentProvider */
        $paymentProvider = $this->addPaymentProvider();

        $subscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => SubscriptionStatus::ACTIVE->value,
            'plan_id' => $plan->id,
            'quantity' => 1,
            'payment_provider_id' => PaymentProvider::where('slug', 'paymore')->first()->id,
        ]);

        $invited = $this->createUser();

        $invitation = Invitation::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $inviter->id,
            'email' => $invited->email,
            'role' => TenancyPermissionConstants::ROLE_ADMIN,
        ]);

        $paymentProvider->shouldReceive('updateSubscriptionQuantity')
            ->once()
            ->with(\Mockery::any(), 2, true)
            ->andReturn(true);
        ;

        // get from the container
        $paymentManager = app(PaymentManager::class);

        $permissionManager = new TenantPermissionManager();
        $tenantManager = new TenantManager(
            $permissionManager,
            new TenantSubscriptionManager($paymentManager),
        );

        Event::fake();

        $result = $tenantManager->acceptInvitation($invitation, $invited);

        $this->assertTrue($result);

        $tenantUsers = $tenant->users()->get();
        $this->assertEquals(2, $tenantUsers->count());

        $userRoles = $permissionManager->getTenantUserRoles($tenant, $invited);
        $this->assertContains(TenancyPermissionConstants::ROLE_ADMIN, $userRoles);

        // make sure that the UserJoinedTenant event was dispatched
        Event::assertDispatched(UserJoinedTenant::class);
    }

    public function test_accept_invitation_flat_rate_plan()
    {
        $tenant = $this->createTenant();
        $inviter = $this->createUser($tenant);

        $plan = Plan::factory()->create([
            'slug' => 'plan-slug-'.uniqid(),
            'is_active' => true,
            'type' => PlanType::FLAT_RATE->value,
        ]);

        PlanPrice::create([
            'plan_id' => $plan->id,
            'currency_id' => Currency::where('code', 'USD')->first()->id,
            'price' => 100,
        ]);

        /** @var PaymentProviderInterface|MockInterface $paymentProvider */
        $paymentProvider = $this->addPaymentProvider();

        $subscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => SubscriptionStatus::ACTIVE->value,
            'plan_id' => $plan->id,
            'quantity' => 1,
            'payment_provider_id' => PaymentProvider::where('slug', 'paymore')->first()->id,
        ]);

        $invited = $this->createUser();

        $invitation = Invitation::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $inviter->id,
            'email' => $invited->email,
            'role' => TenancyPermissionConstants::ROLE_ADMIN,
        ]);

        $paymentProvider->shouldNotReceive('updateSubscriptionQuantity');

        // get from the container
        $paymentManager = app(PaymentManager::class);

        $permissionManager = new TenantPermissionManager();
        $tenantManager = new TenantManager(
            $permissionManager,
            new TenantSubscriptionManager($paymentManager),
        );

        Event::fake();

        $result = $tenantManager->acceptInvitation($invitation, $invited);

        $this->assertTrue($result);

        $tenantUsers = $tenant->users()->get();
        $this->assertEquals(2, $tenantUsers->count());

        $userRoles = $permissionManager->getTenantUserRoles($tenant, $invited);
        $this->assertContains(TenancyPermissionConstants::ROLE_ADMIN, $userRoles);

        // make sure that the UserJoinedTenant event was dispatched
        Event::assertDispatched(UserJoinedTenant::class);
    }

    public function test_accept_invitation_does_not_work_when_tenant_subscription_reached_maximum_user_count()
    {
        $tenant = $this->createTenant();
        $inviter = $this->createUser($tenant);

        $plan = Plan::factory()->create([
            'slug' => 'plan-slug-'.uniqid(),
            'is_active' => true,
            'type' => PlanType::SEAT_BASED->value,
            'max_users_per_tenant' => 1,  // maximum number already reached
        ]);

        PlanPrice::create([
            'plan_id' => $plan->id,
            'currency_id' => Currency::where('code', 'USD')->first()->id,
            'price' => 100,
        ]);

        /** @var PaymentProviderInterface|MockInterface $paymentProvider */
        $paymentProvider = $this->addPaymentProvider();

        $subscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => SubscriptionStatus::ACTIVE->value,
            'plan_id' => $plan->id,
            'quantity' => 1,
            'payment_provider_id' => PaymentProvider::where('slug', 'paymore')->first()->id,
        ]);

        $invited = $this->createUser();

        $invitation = Invitation::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $inviter->id,
            'email' => $invited->email,
            'role' => TenancyPermissionConstants::ROLE_ADMIN,
        ]);

        $paymentProvider->shouldNotReceive('updateSubscriptionQuantity');

        // get from the container
        $paymentManager = app(PaymentManager::class);

        $permissionManager = new TenantPermissionManager();
        $tenantManager = new TenantManager(
            $permissionManager,
            new TenantSubscriptionManager($paymentManager),
        );

        Event::fake();

        $result = $tenantManager->acceptInvitation($invitation, $invited);

        $this->assertFalse($result);

        $tenantUsers = $tenant->users()->get();
        $this->assertEquals(1, $tenantUsers->count());

        // make sure that the UserJoinedTenant event was dispatched
        Event::assertNotDispatched(UserJoinedTenant::class);
    }

    public function test_add_user_to_tenant()
    {
        $tenant = $this->createTenant();

        $plan = Plan::factory()->create([
            'slug' => 'plan-slug-'.uniqid(),
            'is_active' => true,
            'type' => PlanType::SEAT_BASED->value,
        ]);

        PlanPrice::create([
            'plan_id' => $plan->id,
            'currency_id' => Currency::where('code', 'USD')->first()->id,
            'price' => 100,
        ]);

        /** @var PaymentProviderInterface|MockInterface $paymentProvider */
        $paymentProvider = $this->addPaymentProvider();

        $subscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => SubscriptionStatus::ACTIVE->value,
            'plan_id' => $plan->id,
            'quantity' => 1,
            'payment_provider_id' => PaymentProvider::where('slug', 'paymore')->first()->id,
        ]);

        $user = $this->createUser();

        $paymentProvider->shouldReceive('updateSubscriptionQuantity');

        // get from the container
        $paymentManager = app(PaymentManager::class);

        $permissionManager = new TenantPermissionManager();
        $tenantManager = new TenantManager(
            $permissionManager,
            new TenantSubscriptionManager($paymentManager),
        );

        Event::fake();

        $result = $tenantManager->addUserToTenant($tenant, $user, TenancyPermissionConstants::ROLE_ADMIN);

        $this->assertTrue($result);

        $tenantUsers = $tenant->users()->get();
        $this->assertEquals(1, $tenantUsers->count());

        $userRoles = $permissionManager->getTenantUserRoles($tenant, $user);
        $this->assertContains(TenancyPermissionConstants::ROLE_ADMIN, $userRoles);

        // make sure that the UserJoinedTenant event was dispatched
        Event::assertDispatched(UserJoinedTenant::class);
    }

    public function test_remove_user()
    {
        $tenant = $this->createTenant();
        $user1 = $this->createUser($tenant);
        $this->actingAs($user1);

        $user2 = $this->createUser($tenant);

        $plan = Plan::factory()->create([
            'slug' => 'plan-slug-'.uniqid(),
            'is_active' => true,
            'type' => PlanType::SEAT_BASED->value,
        ]);

        PlanPrice::create([
            'plan_id' => $plan->id,
            'currency_id' => Currency::where('code', 'USD')->first()->id,
            'price' => 100,
        ]);

        /** @var PaymentProviderInterface|MockInterface $paymentProvider */
        $paymentProvider = $this->addPaymentProvider();

        $subscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => SubscriptionStatus::ACTIVE->value,
            'plan_id' => $plan->id,
            'quantity' => 2,
            'payment_provider_id' => PaymentProvider::where('slug', 'paymore')->first()->id,
        ]);

        $paymentProvider->shouldReceive('updateSubscriptionQuantity')
            ->once()
            ->with(\Mockery::any(), 1, true)
            ->andReturn(true);
        ;

        // get from the container
        $paymentManager = app(PaymentManager::class);

        $permissionManager = new TenantPermissionManager();
        $tenantManager = new TenantManager(
            $permissionManager,
            new TenantSubscriptionManager($paymentManager),
        );

        Event::fake();

        $result = $tenantManager->removeUser($tenant, $user2);

        $this->assertTrue($result);

        $tenantUsers = $tenant->users()->get();
        $this->assertEquals(1, $tenantUsers->count());

        $userRoles = $permissionManager->getTenantUserRoles($tenant, $user2);
        $this->assertEmpty($userRoles);

        Event::assertDispatched(UserRemovedFromTenant::class);
    }

    public function test_remove_user_cant_remove_last_user_in_tenant()
    {
        $tenant = $this->createTenant();
        $user1 = $this->createUser($tenant);
        $this->actingAs($user1);

        $plan = Plan::factory()->create([
            'slug' => 'plan-slug-'.uniqid(),
            'is_active' => true,
            'type' => PlanType::SEAT_BASED->value,
        ]);

        PlanPrice::create([
            'plan_id' => $plan->id,
            'currency_id' => Currency::where('code', 'USD')->first()->id,
            'price' => 100,
        ]);

        /** @var PaymentProviderInterface|MockInterface $paymentProvider */
        $paymentProvider = $this->addPaymentProvider();

        $subscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => SubscriptionStatus::ACTIVE->value,
            'plan_id' => $plan->id,
            'quantity' => 1,
            'payment_provider_id' => PaymentProvider::where('slug', 'paymore')->first()->id,
        ]);

        $paymentProvider->shouldNotReceive('updateSubscriptionQuantity');

        // get from the container
        $paymentManager = app(PaymentManager::class);

        $permissionManager = new TenantPermissionManager();
        $tenantManager = new TenantManager(
            $permissionManager,
            new TenantSubscriptionManager($paymentManager),
        );

        Event::fake();

        $result = $tenantManager->removeUser($tenant, $user1);

        $this->assertFalse($result);

        $tenantUsers = $tenant->users()->get();
        $this->assertEquals(1, $tenantUsers->count());

        Event::assertNotDispatched(UserRemovedFromTenant::class);
    }


    private function addPaymentProvider()
    {
        // find or create payment provider
        PaymentProvider::updateOrCreate([
            'slug' => 'paymore',
        ], [
            'name' => 'Paymore',
            'is_active' => true,
            'type' => 'any',
        ]);

        $mock = \Mockery::mock(PaymentProviderInterface::class);

        $mock->shouldReceive('getSlug')
            ->andReturn('paymore');

        $this->app->instance(PaymentProviderInterface::class, $mock);

        $this->app->bind(PaymentManager::class, function () use ($mock) {
            return new PaymentManager($mock);
        });

        return $mock;
    }
}
