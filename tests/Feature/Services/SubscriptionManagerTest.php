<?php

namespace Tests\Feature\Services;

use App\Constants\SubscriptionStatus;
use App\Events\Subscription\Subscribed;
use App\Events\Subscription\SubscriptionCancelled;
use App\Events\Subscription\SubscriptionRenewed;
use App\Exceptions\SubscriptionCreationNotAllowedException;
use App\Models\Currency;
use App\Models\Interval;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Services\SubscriptionManager;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tests\Feature\FeatureTest;

class SubscriptionManagerTest extends FeatureTest
{
    /**
     * @dataProvider nonDeadSubscriptionProvider
     */
    public function test_can_only_create_subscription_if_no_other_non_dead_subscription_exists($status)
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);

        $slug = Str::random();
        $plan = Plan::factory()->create([
            'slug' => $slug,
            'is_active' => true,
        ]);

        Subscription::factory()->create([
            'user_id' => $user->id,
            'status' => $status,
            'plan_id' => $plan->id,
            'tenant_id' => $tenant->id,
        ])->save();

        /** @var SubscriptionManager $manager */
        $manager = app()->make(SubscriptionManager::class);

        $this->expectException(SubscriptionCreationNotAllowedException::class);
        $manager->create($slug, $user->id, 1, $tenant);
    }

    public function test_calculate_subscription_trial_days()
    {
        $manager = app()->make(SubscriptionManager::class);

        $plan = Plan::factory()->create([
            'slug' => Str::random(),
            'has_trial' => true,
            'trial_interval_count' => 1,
            'trial_interval_id' => Interval::where('slug', 'day')->first()->id,
        ]);

        $this->assertEquals(1, $manager->calculateSubscriptionTrialDays($plan));

        $plan = Plan::factory()->create([
            'slug' => Str::random(),
            'has_trial' => true,
            'trial_interval_count' => 1,
            'trial_interval_id' => Interval::where('slug', 'week')->first()->id,
        ]);

        $this->assertEquals(7, $manager->calculateSubscriptionTrialDays($plan));

        $plan = Plan::factory()->create([
            'slug' => Str::random(),
            'has_trial' => true,
            'trial_interval_count' => 2,
            'trial_interval_id' => Interval::where('slug', 'week')->first()->id,
        ]);

        $this->assertEquals(14, $manager->calculateSubscriptionTrialDays($plan));

        $plan = Plan::factory()->create([
            'slug' => Str::random(),
            'has_trial' => true,
            'trial_interval_count' => 1,
            'trial_interval_id' => Interval::where('slug', 'month')->first()->id,
        ]);

        $this->assertContains($manager->calculateSubscriptionTrialDays($plan), [28, 29, 30, 31]);

        $plan = Plan::factory()->create([
            'slug' => Str::random(),
            'has_trial' => true,
            'trial_interval_count' => 1,
            'trial_interval_id' => Interval::where('slug', 'year')->first()->id,
        ]);

        $this->assertContains($manager->calculateSubscriptionTrialDays($plan), [365, 366]);
    }

    public function test_create_subscription_in_case_new_subscription_exists()
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);

        $slug = Str::random();
        $plan = Plan::factory()->create([
            'slug' => $slug,
            'is_active' => true,
        ]);

        $planPrice = PlanPrice::factory()->create([
            'plan_id' => $plan->id,
            'price' => 100,
            'currency_id' => Currency::where('code', 'USD')->first()->id,
        ]);

        Subscription::factory()->create([
            'user_id' => $user->id,
            'status' => SubscriptionStatus::NEW->value,
            'plan_id' => $plan->id,
            'tenant_id' => $tenant->id,
        ])->save();

        /** @var SubscriptionManager $manager */
        $manager = app()->make(SubscriptionManager::class);

        $subscription = $manager->create($slug, $user->id, 1, $tenant);

        $this->assertNotNull($subscription);
    }

    public function test_update_subscription_dispatches_subscribed_event()
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);
        $this->actingAs($user);

        $slug = Str::random();
        $plan = Plan::factory()->create([
            'slug' => $slug,
            'is_active' => true,
        ]);

        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'status' => SubscriptionStatus::PENDING->value,
            'plan_id' => $plan->id,
            'tenant_id' => $tenant->id,
        ]);

        /** @var SubscriptionManager $manager */
        $manager = app()->make(SubscriptionManager::class);

        Event::fake();

        $subscription = $manager->updateSubscription($subscription, [
            'status' => SubscriptionStatus::ACTIVE->value,
        ]);

        Event::assertDispatched(Subscribed::class);
    }

    public function test_update_subscription_dispatches_canceled_event()
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);
        $this->actingAs($user);

        $slug = Str::random();
        $plan = Plan::factory()->create([
            'slug' => $slug,
            'is_active' => true,
        ]);

        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'status' => SubscriptionStatus::ACTIVE->value,
            'plan_id' => $plan->id,
            'tenant_id' => $tenant->id,
        ]);

        /** @var SubscriptionManager $manager */
        $manager = app()->make(SubscriptionManager::class);

        Event::fake();

        $subscription = $manager->updateSubscription($subscription, [
            'status' => SubscriptionStatus::CANCELED->value,
        ]);

        Event::assertDispatched(SubscriptionCancelled::class);
    }

    public function test_update_subscription_dispatches_renewed_event()
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);
        $this->actingAs($user);

        $slug = Str::random();
        $plan = Plan::factory()->create([
            'slug' => $slug,
            'is_active' => true,
        ]);

        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'status' => SubscriptionStatus::ACTIVE->value,
            'plan_id' => $plan->id,
            'ends_at' => now(),
            'tenant_id' => $tenant->id,
        ]);

        /** @var SubscriptionManager $manager */
        $manager = app()->make(SubscriptionManager::class);

        Event::fake();

        $subscription = $manager->updateSubscription($subscription, [
            'status' => SubscriptionStatus::ACTIVE->value,
            'ends_at' => now()->addDays(30),
        ]);

        Event::assertDispatched(SubscriptionRenewed::class);
    }

    public static function nonDeadSubscriptionProvider()
    {
        return [
            'pending' => [
                'pending',
            ],
            'active' => [
                'active',
            ],
            'paused' => [
                'paused',
            ],
            'past_due' => [
                'past_due',
            ],
        ];
    }
}
