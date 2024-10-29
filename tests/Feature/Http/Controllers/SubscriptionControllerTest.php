<?php

namespace Tests\Feature\Http\Controllers;

use App\Constants\SubscriptionStatus;
use App\Constants\TenancyPermissionConstants;
use App\Models\PaymentProvider;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Subscription;
use Tests\Feature\FeatureTest;

class SubscriptionControllerTest extends FeatureTest
{
    public function test_change_plan(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant, [TenancyPermissionConstants::PERMISSION_UPDATE_SUBSCRIPTIONS]);
        $this->actingAs($user);

        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'status' => SubscriptionStatus::ACTIVE,
            'payment_provider_id' => PaymentProvider::where('slug', 'stripe')->first()->id,
        ]);

        $newPlan = Plan::factory()->create([
            'is_active' => true,
            'slug' => 'new-plan',
        ]);

        $planPrice = PlanPrice::factory()->create([
            'plan_id' => $newPlan->id,
            'price' => 100,
            'currency_id' => $subscription->currency_id,
        ]);

        $response = $this->get(route('subscription.change-plan', [
            'planSlug' => $newPlan->slug,
            'subscriptionUuid' => $subscription->uuid,
            'tenantUuid' => $tenant->uuid,
        ]));

        $response->assertStatus(200);
    }
}
