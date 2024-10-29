<?php

namespace Tests\Feature\Livewire\Checkout;

use App\Livewire\Checkout\ProductCheckoutForm;
use App\Models\Currency;
use App\Models\OneTimeProduct;
use App\Models\OneTimeProductPrice;
use App\Models\Order;
use App\Models\PaymentProvider;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PaymentProviders\PaymentManager;
use App\Services\PaymentProviders\PaymentProviderInterface;
use Livewire\Livewire;
use Tests\Feature\FeatureTest;

class ProductCheckoutFormTest extends FeatureTest
{
    public function test_can_checkout_new_user()
    {
        $product = OneTimeProduct::factory()->create([
            'slug' => 'product-slug-6',
            'is_active' => true,
        ]);

        OneTimeProductPrice::create([
            'one_time_product_id' => $product->id,
            'currency_id' => Currency::where('code', 'USD')->first()->id,
            'price' => 100,
        ]);

        $this->addPaymentProvider();

        // get number of orders before checkout
        $ordersBefore = Order::count();

        $tenantsBefore = Tenant::count();

        Livewire::test(ProductCheckoutForm::class)
            ->set('name', 'Name')
            ->set('email', 'something@gmail.com')
            ->set('password', 'password')
            ->set('paymentProvider', 'paymore')
            ->call('checkout')
            ->assertRedirect('http://paymore.com/checkout');

        // assert user has been created
        $this->assertDatabaseHas('users', [
            'email' => 'something@gmail.com',
        ]);

        // assert user is logged in
        $this->assertAuthenticated();

        // assert order has been created
        $this->assertEquals($ordersBefore + 1, Order::count());
        $this->assertEquals($tenantsBefore + 1, Tenant::count());
    }

    public function test_can_checkout_existing_user()
    {
        $product = OneTimeProduct::factory()->create([
            'slug' => 'product-slug-7',
            'is_active' => true,
        ]);

        OneTimeProductPrice::create([
            'one_time_product_id' => $product->id,
            'currency_id' => Currency::where('code', 'USD')->first()->id,
            'price' => 100,
        ]);

        $user = User::factory()->create([
            'email' => 'existing@gmail.com',
            'password' => bcrypt('password'),
            'name' => 'Name',
        ]);

        $this->addPaymentProvider();

        // get number of orders before checkout
        $ordersBefore = Order::count();
        $tenantsBefore = Tenant::count();

        Livewire::test(ProductCheckoutForm::class)
            ->set('email', $user->email)
            ->set('password', 'password')
            ->set('paymentProvider', 'paymore')
            ->call('checkout')
            ->assertRedirect('http://paymore.com/checkout');

        // assert user has been created
        $this->assertDatabaseHas('users', [
            'email' => 'something@gmail.com',
        ]);

        // assert order has been created
        $this->assertEquals($ordersBefore + 1, Order::count());
        $this->assertEquals($tenantsBefore + 1, Tenant::count());
    }

    public function test_can_checkout_overlay_payment()
    {
        $product = OneTimeProduct::factory()->create([
            'slug' => 'product-slug-8',
            'is_active' => true,
        ]);

        OneTimeProductPrice::create([
            'one_time_product_id' => $product->id,
            'currency_id' => Currency::where('code', 'USD')->first()->id,
            'price' => 100,
        ]);

        $this->addPaymentProvider(false);

        // get number of orders before checkout
        $ordersBefore = Order::count();
        $tenantsBefore = Tenant::count();

        Livewire::test(ProductCheckoutForm::class)
            ->set('name', 'Name')
            ->set('email', 'something2@gmail.com')
            ->set('password', 'password')
            ->set('paymentProvider', 'paymore')
            ->call('checkout')
            ->assertDispatched('start-overlay-checkout');

        // assert user has been created
        $this->assertDatabaseHas('users', [
            'email' => 'something2@gmail.com',
        ]);

        // assert user is logged in
        $this->assertAuthenticated();

        // assert order has been created
        $this->assertEquals($ordersBefore + 1, Order::count());
        $this->assertEquals($tenantsBefore + 1, Tenant::count());
    }

    private function addPaymentProvider(bool $isRedirect = true)
    {
        // find or create payment provider
        PaymentProvider::updateOrCreate([
            'slug' => 'paymore',
        ], [
            'name' => 'Paymore',
            'is_active' => true,
            'type' => 'any',
        ]);

        $mock = \Mockery::mock(PaymentProviderInterface::class);
        $mock->shouldReceive('initProductCheckout')
            ->once()
            ->andReturn([]);

        $mock->shouldReceive('isRedirectProvider')
            ->andReturn($isRedirect);

        $mock->shouldReceive('getSlug')
            ->andReturn('paymore');

        $mock->shouldReceive('getName')
            ->andReturn('Paymore');

        $mock->shouldReceive('isOverlayProvider')
            ->andReturn(! $isRedirect);

        if ($isRedirect) {
            $mock->shouldReceive('createProductCheckoutRedirectLink')
                ->andReturn('http://paymore.com/checkout');
        }

        $this->app->instance(PaymentProviderInterface::class, $mock);

        $this->app->bind(PaymentManager::class, function () use ($mock) {
            return new PaymentManager($mock);
        });

        return $mock;
    }
}
