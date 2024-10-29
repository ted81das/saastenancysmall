<?php

namespace Database\Factories;

use App\Models\Currency;
use App\Models\Interval;
use App\Models\Plan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'plan_id' => Plan::factory(),
            'price' => 0,
            'currency_id' => Currency::where('code', 'USD')->first()->id,
            'ends_at' => Carbon::now()->addDays(30),
            'cancelled_at' => null,
            'uuid' => (string) Str::uuid(),
            'payment_provider_subscription_id' => null,
            'payment_provider_status' => null,
            'payment_provider_id' => null,
            'trial_ends_at' => null,
            'interval_id' => Interval::where('slug', 'month')->first()->id,
            'interval_count' => 1,
            'is_canceled_at_end_of_cycle' => false,
            'status' => 'active',
        ];
    }
}
