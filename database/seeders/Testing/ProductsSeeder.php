<?php

namespace Database\Seeders\Testing;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // run only in testing environment
        if (app()->environment() !== 'testing') {
            return;
        }

        DB::table('products')->upsert([
            [
                'name' => 'Product 1',
                'slug' => 'product-1',
                'description' => 'Product 1 description',
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Product 2',
                'slug' => 'product-2',
                'description' => 'Product 2 description',
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
        ], ['slug'], ['name', 'description']);
    }
}
