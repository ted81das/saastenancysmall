<?php

namespace Database\Seeders\Testing;

use Database\Seeders\CurrenciesSeeder;
use Database\Seeders\IntervalsSeeder;
use Database\Seeders\PaymentProvidersSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Seeder;

class TestingDatabaseSeeder extends Seeder
{
    /**
     * Seed the testing database.
     */
    public function run(): void
    {
        // run only in testing environment
        if (app()->environment() !== 'testing') {
            return;
        }

        $this->callOnce([
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            IntervalsSeeder::class,
            CurrenciesSeeder::class,
            PaymentProvidersSeeder::class,
            ProductsSeeder::class,
            PlansSeeder::class,
        ]);
    }
}
