<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Currency;
use App\Models\OneTimeProduct;
use App\Models\OneTimeProductPrice;
use Tests\Feature\FeatureTest;

class ProductCheckoutControllerTest extends FeatureTest
{
    public function test_checkout_loads()
    {
        $product = OneTimeProduct::factory()->create([
            'slug' => 'product-slug-5',
            'is_active' => true,
        ]);

        OneTimeProductPrice::create([
            'one_time_product_id' => $product->id,
            'currency_id' => Currency::where('code', 'USD')->first()->id,
            'price' => 100,
        ]);

        $response = $this->followingRedirects()->get(route('buy.product', [
            'productSlug' => $product->slug,
        ]));

        $response->assertStatus(200);

        $response->assertSee('Complete your purchase');
    }
}
