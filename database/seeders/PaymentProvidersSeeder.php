<?php

namespace Database\Seeders;

use App\Constants\PaymentProviderConstants;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentProvidersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('payment_providers')->upsert([
            [
                'name' => 'Stripe',
                'slug' => PaymentProviderConstants::STRIPE_SLUG,
                'type' => 'multi',
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Paddle',
                'slug' => PaymentProviderConstants::PADDLE_SLUG,
                'type' => 'multi',
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Lemon Squeezy',
                'slug' => PaymentProviderConstants::LEMON_SQUEEZY_SLUG,
                'type' => 'multi',
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
        ], ['slug']);

    }
}
