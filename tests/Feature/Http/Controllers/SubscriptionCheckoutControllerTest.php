<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Currency;
use App\Models\Plan;
use App\Models\PlanPrice;
use Tests\Feature\FeatureTest;

class SubscriptionCheckoutControllerTest extends FeatureTest
{
    public function test_checkout_loads()
    {
        $plan = Plan::factory()->create([
            'slug' => 'plan-slug-3',
            'is_active' => true,
        ]);

        PlanPrice::create([
            'plan_id' => $plan->id,
            'currency_id' => Currency::where('code', 'USD')->first()->id,
            'price' => 100,
        ]);

        $response = $this->followingRedirects()->get(route('checkout.subscription', [
            'planSlug' => $plan->slug,
        ]));


        $response->assertStatus(200);

        $response->assertSee('Complete Subscription');

    }
}
