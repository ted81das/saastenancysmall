<?php

namespace Tests\Feature\Livewire\Checkout;

use App\Constants\SessionConstants;
use App\Dto\SubscriptionCheckoutDto;
use App\Livewire\Checkout\SubscriptionCheckoutForm;
use App\Models\Currency;
use App\Models\PaymentProvider;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PaymentProviders\PaymentManager;
use App\Services\PaymentProviders\PaymentProviderInterface;
use Livewire\Livewire;
use Tests\Feature\FeatureTest;

class SubscriptionCheckoutFormTest extends FeatureTest
{
    public function test_can_checkout_new_user()
    {
        $sessionDto = new SubscriptionCheckoutDto();
        $sessionDto->planSlug = 'plan-slug-5';

        $this->withSession([SessionConstants::SUBSCRIPTION_CHECKOUT_DTO => $sessionDto]);

        $plan = Plan::factory()->create([
            'slug' => 'plan-slug-5',
            'is_active' => true,
        ]);

        PlanPrice::create([
            'plan_id' => $plan->id,
            'currency_id' => Currency::where('code', 'USD')->first()->id,
            'price' => 100,
        ]);

        $this->addPaymentProvider();

        // get number of subscriptions before checkout
        $subscriptionsBefore = Subscription::count();
        $tenantsBefore = Tenant::count();

        Livewire::test(SubscriptionCheckoutForm::class)
            ->set('name', 'Name')
            ->set('email', 'something+sub1@gmail.com')
            ->set('password', 'password')
            ->set('paymentProvider', 'paymore')
            ->call('checkout')
            ->assertRedirect('http://paymore.com/checkout');

        // assert user has been created
        $this->assertDatabaseHas('users', [
            'email' => 'something+sub1@gmail.com',
        ]);

        // assert user is logged in
        $this->assertAuthenticated();

        // assert order has been created
        $this->assertEquals($subscriptionsBefore + 1, Subscription::count());
        $this->assertEquals($tenantsBefore + 1, Tenant::count());
    }

    public function test_can_checkout_existing_user()
    {
        $sessionDto = new SubscriptionCheckoutDto();
        $sessionDto->planSlug = 'plan-slug-6';

        $this->withSession([SessionConstants::SUBSCRIPTION_CHECKOUT_DTO => $sessionDto]);

        $plan = Plan::factory()->create([
            'slug' => 'plan-slug-6',
            'is_active' => true,
        ]);

        PlanPrice::create([
            'plan_id' => $plan->id,
            'currency_id' => Currency::where('code', 'USD')->first()->id,
            'price' => 100,
        ]);

        $user = User::factory()->create([
            'email' => 'existing+sub1@gmail.com',
            'password' => bcrypt('password'),
            'name' => 'Name',
        ]);

        $this->addPaymentProvider();

        // get number of subscriptions before checkout
        $subscriptionsBefore = Subscription::count();
        $tenantsBefore = Tenant::count();

        Livewire::test(SubscriptionCheckoutForm::class)
            ->set('name', 'Name')
            ->set('email', 'existing+sub1@gmail.com')
            ->set('password', 'password')
            ->set('paymentProvider', 'paymore')
            ->call('checkout')
            ->assertRedirect('http://paymore.com/checkout');

        // assert user has been created
        $this->assertDatabaseHas('users', [
            'email' => 'existing+sub1@gmail.com',
        ]);

        // assert user is logged in
        $this->assertAuthenticated();

        // assert order has been created
        $this->assertEquals($subscriptionsBefore + 1, Subscription::count());
        $this->assertEquals($tenantsBefore + 1, Tenant::count());
    }

    public function test_can_checkout_overlay_payment()
    {
        $sessionDto = new SubscriptionCheckoutDto();
        $sessionDto->planSlug = 'plan-slug-7';

        $this->withSession([SessionConstants::SUBSCRIPTION_CHECKOUT_DTO => $sessionDto]);

        $plan = Plan::factory()->create([
            'slug' => 'plan-slug-7',
            'is_active' => true,
        ]);

        PlanPrice::create([
            'plan_id' => $plan->id,
            'currency_id' => Currency::where('code', 'USD')->first()->id,
            'price' => 100,
        ]);

        $this->addPaymentProvider(false);

        // get number of subscriptions before checkout
        $subscriptionsBefore = Subscription::count();
        $tenantsBefore = Tenant::count();

        Livewire::test(SubscriptionCheckoutForm::class)
            ->set('name', 'Name')
            ->set('email', 'something+sub2@gmail.com')
            ->set('password', 'password')
            ->set('paymentProvider', 'paymore')
            ->call('checkout')
            ->assertDispatched('start-overlay-checkout');

        // assert user has been created
        $this->assertDatabaseHas('users', [
            'email' => 'something+sub2@gmail.com',
        ]);

        // assert user is logged in
        $this->assertAuthenticated();

        // assert order has been created
        $this->assertEquals($subscriptionsBefore + 1, Subscription::count());
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
        $mock->shouldReceive('initSubscriptionCheckout')
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
            $mock->shouldReceive('createSubscriptionCheckoutRedirectLink')
                ->andReturn('http://paymore.com/checkout');
        }

        $this->app->instance(PaymentProviderInterface::class, $mock);

        $this->app->bind(PaymentManager::class, function () use ($mock) {
            return new PaymentManager($mock);
        });

        return $mock;
    }
}
