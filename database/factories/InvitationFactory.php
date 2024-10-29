<?php

namespace Database\Factories;

use App\Constants\InvitationStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invitation>
 */
class InvitationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'token' => fake()->sha256(),
            'status' => InvitationStatus::PENDING->value,
            'expires_at' => now()->addWeek(),
        ];
    }
}
