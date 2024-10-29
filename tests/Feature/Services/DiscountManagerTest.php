<?php

namespace Tests\Feature\Services;

use App\Models\Discount;
use App\Models\OneTimeProduct;
use App\Models\Plan;
use App\Models\User;
use App\Services\DiscountManager;
use Illuminate\Support\Str;
use Tests\Feature\FeatureTest;

class DiscountManagerTest extends FeatureTest
{
    public function test_is_code_redeemable_for_plan()
    {
        // add a discount
        $discount = Discount::create([
            'name' => 'test',
            'description' => 'test',
            'type' => 'percentage',
            'amount' => 10,
            'is_active' => true,
            'valid_until' => null,
            'action_type' => null,
            'max_redemptions' => -1,
            'max_redemptions_per_user' => -1,
            'is_recurring' => false,
            'redemptions' => 0,
        ]);

        $code = Str::random(10);
        $discountCode = $discount->codes()->create([
            'code' => $code,
        ]);

        $user = User::factory()->create();

        // find plan from database
        $plan = Plan::take(1)->first();

        $discountManager = app()->make(DiscountManager::class);

        $this->assertTrue($discountManager->isCodeRedeemableForPlan($code, $user, $plan));
    }

    public function test_is_code_redeemable_for_one_time_product()
    {
        // add a discount
        $discount = Discount::create([
            'name' => 'test',
            'description' => 'test',
            'type' => 'percentage',
            'amount' => 10,
            'is_active' => true,
            'valid_until' => null,
            'action_type' => null,
            'max_redemptions' => -1,
            'max_redemptions_per_user' => -1,
            'is_recurring' => false,
            'redemptions' => 0,
        ]);

        $code = Str::random(10);
        $discountCode = $discount->codes()->create([
            'code' => $code,
        ]);

        $user = User::factory()->create();

        $oneTimeProduct = OneTimeProduct::factory()->create();

        $discountManager = app()->make(DiscountManager::class);

        $this->assertTrue($discountManager->isCodeRedeemableForOneTimeProduct($code, $user, $oneTimeProduct));
    }

    public function test_is_code_not_redeemable_for_one_time_product_because_product_is_not_included_in_list()
    {
        // add a discount
        $discount = Discount::create([
            'name' => 'test',
            'description' => 'test',
            'type' => 'percentage',
            'amount' => 10,
            'is_active' => true,
            'valid_until' => null,
            'action_type' => null,
            'max_redemptions' => -1,
            'max_redemptions_per_user' => -1,
            'is_recurring' => false,
            'redemptions' => 0,
        ]);

        // add a one-time product
        $oneTimeProduct1 = OneTimeProduct::factory()->create();
        $oneTimeProduct2 = OneTimeProduct::factory()->create();

        $discount->oneTimeProducts()->attach($oneTimeProduct1);

        $code = Str::random(10);
        $discountCode = $discount->codes()->create([
            'code' => $code,
        ]);

        $user = User::factory()->create();

        $discountManager = app()->make(DiscountManager::class);

        $this->assertFalse($discountManager->isCodeRedeemableForOneTimeProduct($code, $user, $oneTimeProduct2));
    }

    public function test_is_code_not_redeemable_for_plan_because_of_valid_until()
    {
        // add a discount
        $discount = Discount::create([
            'name' => 'test',
            'description' => 'test',
            'type' => 'percentage',
            'amount' => 10,
            'is_active' => true,
            'valid_until' => now()->subDay(),
            'action_type' => null,
            'max_redemptions' => -1,
            'max_redemptions_per_user' => -1,
            'is_recurring' => false,
            'redemptions' => 0,
        ]);

        $code = Str::random(10);
        $discountCode = $discount->codes()->create([
            'code' => $code,
        ]);

        $user = User::factory()->create();

        // find plan from database
        $plan = Plan::take(1)->first();

        $discountManager = app()->make(DiscountManager::class);

        $this->assertFalse($discountManager->isCodeRedeemableForPlan($code, $user, $plan));
    }

    public function test_is_code_not_redeemable_for_plan_because_of_max_redemptions()
    {
        // add a discount
        $discount = Discount::create([
            'name' => 'test',
            'description' => 'test',
            'type' => 'percentage',
            'amount' => 10,
            'is_active' => true,
            'valid_until' => null,
            'action_type' => null,
            'max_redemptions' => 1,
            'max_redemptions_per_user' => -1,
            'is_recurring' => false,
            'redemptions' => 2,
        ]);

        $code = Str::random(10);
        $discountCode = $discount->codes()->create([
            'code' => $code,
        ]);

        $user = User::factory()->create();

        // find plan from database
        $plan = Plan::take(1)->first();

        $discountManager = app()->make(DiscountManager::class);

        $this->assertFalse($discountManager->isCodeRedeemableForPlan($code, $user, $plan));
    }

    public function test_is_code_not_redeemable_for_plan_because_of_is_active()
    {
        // add a discount
        $discount = Discount::create([
            'name' => 'test',
            'description' => 'test',
            'type' => 'percentage',
            'amount' => 10,
            'is_active' => false,
            'valid_until' => null,
            'action_type' => null,
            'max_redemptions' => -1,
            'max_redemptions_per_user' => -1,
            'is_recurring' => false,
            'redemptions' => 0,
        ]);

        $code = Str::random(10);
        $discountCode = $discount->codes()->create([
            'code' => $code,
        ]);

        $user = User::factory()->create();

        // find plan from database
        $plan = Plan::take(1)->first();

        $discountManager = app()->make(DiscountManager::class);

        $this->assertFalse($discountManager->isCodeRedeemableForPlan($code, $user, $plan));
    }

    public function test_is_code_not_redeemable_for_plan_because_of_max_redemptions_per_user()
    {
        // add a discount
        $discount = Discount::create([
            'name' => 'test',
            'description' => 'test',
            'type' => 'percentage',
            'amount' => 10,
            'is_active' => true,
            'valid_until' => null,
            'action_type' => null,
            'max_redemptions' => -1,
            'max_redemptions_per_user' => 1,
            'is_recurring' => false,
            'redemptions' => 0,
        ]);

        $code = Str::random(10);
        $discountCode = $discount->codes()->create([
            'code' => $code,
        ]);

        $user = User::factory()->create();

        // find plan from database
        $plan = Plan::take(1)->first();

        $discountManager = app()->make(DiscountManager::class);

        $discountCode->redemptions()->create([
            'user_id' => $user->id,
        ]);

        $this->assertFalse($discountManager->isCodeRedeemableForPlan($code, $user, $plan));
    }

    public function test_is_code_not_redeemable_because_of_plan()
    {
        // add a discount
        $discount = Discount::create([
            'name' => 'test',
            'description' => 'test',
            'type' => 'percentage',
            'amount' => 10,
            'is_active' => true,
            'valid_until' => null,
            'action_type' => null,
            'max_redemptions' => -1,
            'max_redemptions_per_user' => -1,
            'is_recurring' => false,
            'redemptions' => 0,
        ]);

        $code = Str::random(10);
        $discountCode = $discount->codes()->create([
            'code' => $code,
        ]);

        $user = User::factory()->create();

        // find plan from database
        $plan = Plan::where('slug', 'basic')->first();

        $discount->plans()->attach($plan);

        $plan2 = Plan::where('slug', 'pro')->first();

        $discountManager = app()->make(DiscountManager::class);

        $this->assertFalse($discountManager->isCodeRedeemableForPlan($code, $user, $plan2));
    }



}
