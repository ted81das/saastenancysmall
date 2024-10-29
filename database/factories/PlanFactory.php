<?php

namespace Database\Factories;

use App\Models\Interval;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'description' => fake()->sentence(),
            'slug' => fake()->slug(),
            'product_id' => Product::factory(),
            'interval_id' => Interval::where('slug', 'month')->first()->id,
            'interval_count' => 1,
            'has_trial' => false,
            'trial_interval_id' => null,
            'trial_interval_count' => null,
            'is_active' => true,
        ];
    }
}
