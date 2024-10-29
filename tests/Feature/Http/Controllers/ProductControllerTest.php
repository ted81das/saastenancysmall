<?php

namespace Tests\Feature\Http\Controllers;

use App\Constants\SubscriptionStatus;
use App\Models\Currency;
use App\Models\OneTimeProduct;
use App\Models\OneTimeProductPrice;
use App\Models\PaymentProvider;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Subscription;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\Feature\FeatureTest;

class ProductControllerTest extends FeatureTest
{
    public function test_product_checkout_error_for_product_with_no_prices(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $product = OneTimeProduct::factory()->create([
            'slug' => 'product-slug',
            'is_active' => true,
        ]);

        $this->expectException(NotFoundHttpException::class);

        $response = $this->get(route('buy.product', [
            'productSlug' => $product->slug,
        ]));
    }

    public function test_product_checkout_success_for_product_with_prices(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $product = OneTimeProduct::factory()->create([
            'slug' => 'product-slug1',
            'is_active' => true,
        ]);

        OneTimeProductPrice::create([
            'one_time_product_id' => $product->id,
            'currency_id' => Currency::where('code', 'USD')->first()->id,
            'price' => 100,
        ]);

        $response = $this->get(route('buy.product', [
            'productSlug' => $product->slug,
        ]));

        $response->assertRedirectToRoute('checkout.product');
    }
}
