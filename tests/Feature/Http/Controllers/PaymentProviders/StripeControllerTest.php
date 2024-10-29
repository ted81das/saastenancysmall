<?php

namespace Tests\Feature\Http\Controllers\PaymentProviders;

use App\Constants\OrderStatus;
use App\Constants\SubscriptionStatus;
use App\Constants\TransactionStatus;
use App\Models\Currency;
use App\Models\Order;
use App\Models\PaymentProvider;
use App\Models\Subscription;
use App\Services\OrderManager;
use App\Services\PaymentProviders\Stripe\StripeWebhookHandler;
use App\Services\SubscriptionManager;
use App\Services\TransactionManager;
use Illuminate\Support\Str;
use Tests\Feature\FeatureTest;

class StripeControllerTest extends FeatureTest
{
    public function test_subscription_created_webhook(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);

        $uuid = (string) Str::uuid();
        Subscription::create([
            'uuid' => $uuid,
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'price' => 10,
            'currency_id' => 1,
            'plan_id' => 1,
            'interval_id' => 2,
            'interval_count' => 1,
            'status' => SubscriptionStatus::NEW->value,
        ]);

        $payload = $this->getStripeSubscription('incomplete', 'customer.subscription.created', $uuid);

        $timestamp = time();
        $payloadString = json_encode($payload);
        $signature = \hash_hmac('sha256', "{$timestamp}.{$payloadString}", config('services.stripe.webhook_signing_secret'));

        $response = $this->postJson(route('payments-providers.stripe.webhook'), $payload, [
            'Stripe-Signature' => 't='.$timestamp.',v1='.$signature,
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('subscriptions', [
            'uuid' => $uuid,
            'status' => SubscriptionStatus::INACTIVE->value,
        ]);
    }

    public function test_subscription_updated_webhook(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);

        $uuid = (string) Str::uuid();
        Subscription::create([
            'uuid' => $uuid,
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'price' => 10,
            'currency_id' => 1,
            'plan_id' => 1,
            'interval_id' => 2,
            'interval_count' => 1,
            'status' => SubscriptionStatus::INACTIVE->value,
            'quantity' => 1,
        ]);

        $payload = $this->getStripeSubscription('active', 'customer.subscription.updated', $uuid, quantity: 2);

        $timestamp = time();
        $payloadString = json_encode($payload);
        $signature = \hash_hmac('sha256', "{$timestamp}.{$payloadString}", config('services.stripe.webhook_signing_secret'));

        $response = $this->postJson(route('payments-providers.stripe.webhook'), $payload, [
            'Stripe-Signature' => 't='.$timestamp.',v1='.$signature,
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('subscriptions', [
            'uuid' => $uuid,
            'status' => SubscriptionStatus::ACTIVE->value,
            'quantity' => 2,
        ]);
    }

    public function test_subscription_deleted_webhook(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);

        $uuid = (string) Str::uuid();
        Subscription::create([
            'uuid' => $uuid,
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'price' => 10,
            'currency_id' => 1,
            'plan_id' => 1,
            'interval_id' => 2,
            'interval_count' => 1,
            'status' => SubscriptionStatus::ACTIVE->value,
        ]);

        $payload = $this->getStripeSubscription('canceled', 'customer.subscription.deleted', $uuid);

        $timestamp = time();
        $payloadString = json_encode($payload);
        $signature = \hash_hmac('sha256', "{$timestamp}.{$payloadString}", config('services.stripe.webhook_signing_secret'));

        $response = $this->postJson(route('payments-providers.stripe.webhook'), $payload, [
            'Stripe-Signature' => 't='.$timestamp.',v1='.$signature,
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('subscriptions', [
            'uuid' => $uuid,
            'status' => SubscriptionStatus::CANCELED->value,
        ]);
    }

    public function test_subscription_paused_webhook(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);

        $uuid = (string) Str::uuid();
        Subscription::create([
            'uuid' => $uuid,
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'price' => 10,
            'currency_id' => 1,
            'plan_id' => 1,
            'interval_id' => 2,
            'interval_count' => 1,
            'status' => SubscriptionStatus::ACTIVE->value,
        ]);

        $payload = $this->getStripeSubscription('paused', 'customer.subscription.paused', $uuid);

        $timestamp = time();
        $payloadString = json_encode($payload);
        $signature = \hash_hmac('sha256', "{$timestamp}.{$payloadString}", config('services.stripe.webhook_signing_secret'));

        $response = $this->postJson(route('payments-providers.stripe.webhook'), $payload, [
            'Stripe-Signature' => 't='.$timestamp.',v1='.$signature,
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('subscriptions', [
            'uuid' => $uuid,
            'status' => SubscriptionStatus::PAUSED->value,
        ]);
    }

    public function test_subscription_resumed_webhook(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);

        $uuid = (string) Str::uuid();
        Subscription::create([
            'uuid' => $uuid,
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'price' => 10,
            'currency_id' => 1,
            'plan_id' => 1,
            'interval_id' => 2,
            'interval_count' => 1,
            'status' => SubscriptionStatus::ACTIVE->value,
        ]);

        $payload = $this->getStripeSubscription('active', 'customer.subscription.resumed', $uuid);

        $timestamp = time();
        $payloadString = json_encode($payload);
        $signature = \hash_hmac('sha256', "{$timestamp}.{$payloadString}", config('services.stripe.webhook_signing_secret'));

        $response = $this->postJson(route('payments-providers.stripe.webhook'), $payload, [
            'Stripe-Signature' => 't='.$timestamp.',v1='.$signature,
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('subscriptions', [
            'uuid' => $uuid,
            'status' => SubscriptionStatus::ACTIVE->value,
        ]);
    }

    public function test_invoice_created_webhook(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);

        $uuid = (string) Str::uuid();
        $subscription = Subscription::create([
            'uuid' => $uuid,
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'price' => 10,
            'currency_id' => 1,
            'plan_id' => 1,
            'interval_id' => 2,
            'interval_count' => 1,
            'status' => SubscriptionStatus::ACTIVE->value,
        ]);

        $invoiceId = Str::random();
        $payload = $this->getStripeInvoice('open', 'invoice.created', $uuid, $invoiceId);

        $timestamp = time();
        $payloadString = json_encode($payload);
        $signature = \hash_hmac('sha256', "{$timestamp}.{$payloadString}", config('services.stripe.webhook_signing_secret'));

        $response = $this->postJson(route('payments-providers.stripe.webhook'), $payload, [
            'Stripe-Signature' => 't='.$timestamp.',v1='.$signature,
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'subscription_id' => $subscription->id,
            'status' => TransactionStatus::NOT_STARTED->value,
            'payment_provider_transaction_id' => $invoiceId,
            'payment_provider_status' => 'open',
        ]);
    }

    public function test_invoice_updated_webhook(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);

        $uuid = (string) Str::uuid();
        $subscription = Subscription::create([
            'uuid' => $uuid,
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'price' => 10,
            'currency_id' => 1,
            'plan_id' => 1,
            'interval_id' => 2,
            'interval_count' => 1,
            'status' => SubscriptionStatus::ACTIVE->value,
        ]);

        $invoiceId = Str::random();

        $transaction = $subscription->transactions()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $subscription->user_id,
            'tenant_id' => $subscription->tenant_id,
            'currency_id' => $subscription->currency_id,
            'amount' => $subscription->price,
            'status' => TransactionStatus::NOT_STARTED->value,
            'subscription_id' => $subscription->id,
            'payment_provider_id' => PaymentProvider::where('slug', 'stripe')->firstOrFail()->id,
            'payment_provider_status' => 'open',
            'payment_provider_transaction_id' => $invoiceId,
        ]);

        $payload = $this->getStripeInvoice(
            'paid',
            'invoice.updated',
            $uuid,
            $invoiceId,
        );

        $timestamp = time();
        $payloadString = json_encode($payload);
        $signature = \hash_hmac('sha256', "{$timestamp}.{$payloadString}", config('services.stripe.webhook_signing_secret'));

        $mock = \Mockery::mock(StripeWebhookHandler::class, [resolve(SubscriptionManager::class), resolve(TransactionManager::class), resolve(OrderManager::class)])->makePartial()->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('calculateFees')->once()->andReturn(0);
        $this->app->instance(StripeWebhookHandler::class, $mock);

        $response = $this->postJson(route('payments-providers.stripe.webhook'), $payload, [
            'Stripe-Signature' => 't='.$timestamp.',v1='.$signature,
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'subscription_id' => $subscription->id,
            'status' => TransactionStatus::SUCCESS->value,
            'payment_provider_transaction_id' => $invoiceId,
            'payment_provider_status' => 'paid',
        ]);
    }

    public function test_invoice_paid_webhook(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);

        $uuid = (string) Str::uuid();
        $subscription = Subscription::create([
            'uuid' => $uuid,
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'price' => 10,
            'currency_id' => 1,
            'plan_id' => 1,
            'interval_id' => 2,
            'interval_count' => 1,
            'status' => SubscriptionStatus::ACTIVE->value,
        ]);

        $invoiceId = Str::random();

        $transaction = $subscription->transactions()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $subscription->user_id,
            'tenant_id' => $subscription->tenant_id,
            'currency_id' => $subscription->currency_id,
            'amount' => $subscription->price,
            'status' => TransactionStatus::NOT_STARTED->value,
            'subscription_id' => $subscription->id,
            'payment_provider_id' => PaymentProvider::where('slug', 'stripe')->firstOrFail()->id,
            'payment_provider_status' => 'open',
            'payment_provider_transaction_id' => $invoiceId,
        ]);

        $payload = $this->getStripeInvoice(
            'paid',
            'invoice.paid',
            $uuid,
            $invoiceId,
        );

        $timestamp = time();
        $payloadString = json_encode($payload);
        $signature = \hash_hmac('sha256', "{$timestamp}.{$payloadString}", config('services.stripe.webhook_signing_secret'));

        $mock = \Mockery::mock(StripeWebhookHandler::class, [resolve(SubscriptionManager::class), resolve(TransactionManager::class), resolve(OrderManager::class)])->makePartial()->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('calculateFees')->once()->andReturn(0);
        $this->app->instance(StripeWebhookHandler::class, $mock);

        $response = $this->postJson(route('payments-providers.stripe.webhook'), $payload, [
            'Stripe-Signature' => 't='.$timestamp.',v1='.$signature,
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'subscription_id' => $subscription->id,
            'status' => TransactionStatus::SUCCESS->value,
            'payment_provider_transaction_id' => $invoiceId,
            'payment_provider_status' => 'paid',
        ]);
    }

    public function test_invoice_payment_failed_webhook(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);

        $uuid = (string) Str::uuid();
        $subscription = Subscription::create([
            'uuid' => $uuid,
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'price' => 10,
            'currency_id' => 1,
            'plan_id' => 1,
            'interval_id' => 2,
            'interval_count' => 1,
            'status' => SubscriptionStatus::ACTIVE->value,
        ]);

        $invoiceId = Str::random();

        $transaction = $subscription->transactions()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $subscription->user_id,
            'tenant_id' => $subscription->tenant_id,
            'currency_id' => $subscription->currency_id,
            'amount' => $subscription->price,
            'status' => TransactionStatus::NOT_STARTED->value,
            'subscription_id' => $subscription->id,
            'payment_provider_id' => PaymentProvider::where('slug', 'stripe')->firstOrFail()->id,
            'payment_provider_status' => 'open',
            'payment_provider_transaction_id' => $invoiceId,
        ]);

        $payload = $this->getStripeInvoice(
            'void',
            'invoice.payment_failed',
            $uuid,
            $invoiceId,
        );

        $timestamp = time();
        $payloadString = json_encode($payload);
        $signature = \hash_hmac('sha256', "{$timestamp}.{$payloadString}", config('services.stripe.webhook_signing_secret'));

        $response = $this->postJson(route('payments-providers.stripe.webhook'), $payload, [
            'Stripe-Signature' => 't='.$timestamp.',v1='.$signature,
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'subscription_id' => $subscription->id,
            'status' => TransactionStatus::FAILED->value,
            'payment_provider_transaction_id' => $invoiceId,
            'payment_provider_status' => 'void',
        ]);
    }

    public function test_invoice_payment_action_required_webhook(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);

        $uuid = (string) Str::uuid();
        $subscription = Subscription::create([
            'uuid' => $uuid,
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'price' => 10,
            'currency_id' => 1,
            'plan_id' => 1,
            'interval_id' => 2,
            'interval_count' => 1,
            'status' => SubscriptionStatus::ACTIVE->value,
        ]);

        $invoiceId = Str::random();

        $transaction = $subscription->transactions()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $subscription->user_id,
            'tenant_id' => $subscription->tenant_id,
            'currency_id' => $subscription->currency_id,
            'amount' => $subscription->price,
            'status' => TransactionStatus::NOT_STARTED->value,
            'subscription_id' => $subscription->id,
            'payment_provider_id' => PaymentProvider::where('slug', 'stripe')->firstOrFail()->id,
            'payment_provider_status' => 'open',
            'payment_provider_transaction_id' => $invoiceId,
        ]);

        $payload = $this->getStripeInvoice(
            'pending',
            'invoice.payment_action_required',
            $uuid,
            $invoiceId,
        );

        $timestamp = time();
        $payloadString = json_encode($payload);
        $signature = \hash_hmac('sha256', "{$timestamp}.{$payloadString}", config('services.stripe.webhook_signing_secret'));

        $response = $this->postJson(route('payments-providers.stripe.webhook'), $payload, [
            'Stripe-Signature' => 't='.$timestamp.',v1='.$signature,
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'subscription_id' => $subscription->id,
            'status' => TransactionStatus::PENDING->value,
            'payment_provider_transaction_id' => $invoiceId,
            'payment_provider_status' => 'pending',
        ]);
    }

    public function test_payment_intent_success_webhook()
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);

        $user = $this->createUser();
        $currency = Currency::where('code', 'USD')->firstOrFail();
        $orderUUID = (string) Str::uuid();
        $order = Order::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'uuid' => $orderUUID,
            'status' => 'new',
            'currency_id' => $currency->id,
            'total_amount' => 100,
        ]);

        $paymentIntentId = 'pi_3Okqv0JQC7CL5JsV2lwbUtu66';
        $payload = $this->getStripePaymentIntentSucceeded($paymentIntentId, $orderUUID);

        $timestamp = time();
        $payloadString = json_encode($payload);
        $signature = \hash_hmac('sha256', "{$timestamp}.{$payloadString}", config('services.stripe.webhook_signing_secret'));

        $mock = \Mockery::mock(StripeWebhookHandler::class, [resolve(SubscriptionManager::class), resolve(TransactionManager::class), resolve(OrderManager::class)])->makePartial()->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('calculateFees')->once()->andReturn(0);
        $this->app->instance(StripeWebhookHandler::class, $mock);

        $response = $this->postJson(route('payments-providers.stripe.webhook'), $payload, [
            'Stripe-Signature' => 't='.$timestamp.',v1='.$signature,
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'order_id' => $order->id,
            'status' => TransactionStatus::SUCCESS->value,
            'payment_provider_transaction_id' => $paymentIntentId,
            'payment_provider_status' => 'succeeded',
            'tenant_id' => $tenant->id,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::SUCCESS->value,
        ]);

    }

    public function test_charge_refunded_webhook()
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);

        $user = $this->createUser();
        $currency = Currency::where('code', 'USD')->firstOrFail();
        $orderUUID = (string) Str::uuid();
        $order = Order::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'uuid' => $orderUUID,
            'status' => OrderStatus::SUCCESS,
            'currency_id' => $currency->id,
            'total_amount' => 100,
        ]);

        $paymentIntentId = 'pi_3Okqv0JQC7CL5JsV2lwbUtu67';

        $transaction = $order->transactions()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'currency_id' => $currency->id,
            'amount' => 100,
            'status' => TransactionStatus::SUCCESS->value,
            'payment_provider_id' => PaymentProvider::where('slug', 'stripe')->firstOrFail()->id,
            'payment_provider_status' => 'success',
            'payment_provider_transaction_id' => $paymentIntentId,
        ]);

        $payload = $this->getStripeCharge($paymentIntentId, 'charge.refunded', $orderUUID);

        $timestamp = time();
        $payloadString = json_encode($payload);
        $signature = \hash_hmac('sha256', "{$timestamp}.{$payloadString}", config('services.stripe.webhook_signing_secret'));

        $mock = \Mockery::mock(StripeWebhookHandler::class, [resolve(SubscriptionManager::class), resolve(TransactionManager::class), resolve(OrderManager::class)])->makePartial()->shouldAllowMockingProtectedMethods();
        $this->app->instance(StripeWebhookHandler::class, $mock);

        $response = $this->postJson(route('payments-providers.stripe.webhook'), $payload, [
            'Stripe-Signature' => 't='.$timestamp.',v1='.$signature,
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'order_id' => $order->id,
            'status' => TransactionStatus::REFUNDED->value,
            'payment_provider_transaction_id' => $paymentIntentId,
            'payment_provider_status' => 'refunded',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::REFUNDED->value,
        ]);
    }

    public function test_payment_intent_payment_failed_webhook()
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);

        $currency = Currency::where('code', 'USD')->firstOrFail();
        $orderUUID = (string) Str::uuid();
        $order = Order::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'uuid' => $orderUUID,
            'status' => OrderStatus::NEW,
            'currency_id' => $currency->id,
            'total_amount' => 100,
        ]);

        $paymentIntentId = 'pi_3OlDhGJQC7CL5JsV0c0a4vVU';

        $payload = $this->getStripePaymentIntentPaymentFailed($paymentIntentId, $orderUUID);

        $timestamp = time();
        $payloadString = json_encode($payload);
        $signature = \hash_hmac('sha256', "{$timestamp}.{$payloadString}", config('services.stripe.webhook_signing_secret'));

        $mock = \Mockery::mock(StripeWebhookHandler::class, [resolve(SubscriptionManager::class), resolve(TransactionManager::class), resolve(OrderManager::class)])->makePartial()->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('calculateFees')->once()->andReturn(0);
        $this->app->instance(StripeWebhookHandler::class, $mock);

        $response = $this->postJson(route('payments-providers.stripe.webhook'), $payload, [
            'Stripe-Signature' => 't='.$timestamp.',v1='.$signature,
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'order_id' => $order->id,
            'status' => TransactionStatus::FAILED->value,
            'payment_provider_transaction_id' => $paymentIntentId,
            'tenant_id' => $tenant->id,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::FAILED->value,
        ]);
    }

    public function test_dispute_created_webhook()
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);

        $currency = Currency::where('code', 'USD')->firstOrFail();
        $orderUUID = (string) Str::uuid();
        $order = Order::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'uuid' => $orderUUID,
            'status' => OrderStatus::NEW,
            'currency_id' => $currency->id,
            'total_amount' => 100,
        ]);

        $paymentIntentId = 'pi_3OlDhGJQC7CL5JsV0c0a4vV34';

        $transaction = $order->transactions()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'currency_id' => $currency->id,
            'amount' => 100,
            'status' => TransactionStatus::SUCCESS->value,
            'payment_provider_id' => PaymentProvider::where('slug', 'stripe')->firstOrFail()->id,
            'payment_provider_status' => 'success',
            'payment_provider_transaction_id' => $paymentIntentId,
        ]);

        $payload = $this->getStripeDispute($paymentIntentId);

        $timestamp = time();
        $payloadString = json_encode($payload);
        $signature = \hash_hmac('sha256', "{$timestamp}.{$payloadString}", config('services.stripe.webhook_signing_secret'));

        $mock = \Mockery::mock(StripeWebhookHandler::class, [resolve(SubscriptionManager::class), resolve(TransactionManager::class), resolve(OrderManager::class)])->makePartial()->shouldAllowMockingProtectedMethods();
        $this->app->instance(StripeWebhookHandler::class, $mock);

        $response = $this->postJson(route('payments-providers.stripe.webhook'), $payload, [
            'Stripe-Signature' => 't='.$timestamp.',v1='.$signature,
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'order_id' => $order->id,
            'status' => TransactionStatus::DISPUTED->value,
            'payment_provider_transaction_id' => $paymentIntentId,
            'tenant_id' => $tenant->id,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::DISPUTED->value,
        ]);
    }

    public function getStripeCharge(string $paymentIntentId, string $type, string $orderUUID)
    {
        $json = <<<JSON
        {
          "type": "$type",
          "id": "evt_1J5X2n2eZvKYlo2C0Q2Z2Z2Z",
          "object": "event",
          "api_version": "2020-08-27",
          "created": 1632830000,
          "data": {
            "object": {
                "id": "ch_3OklVUJQC7CL5JsV0q7nOBqG",
                "object": "charge",
                "livemode": false,
                "payment_intent": "$paymentIntentId",
                "status": "succeeded",
                "amount": 20000,
                "amount_captured": 20000,
                "amount_refunded": 20000,
                "application": null,
                "application_fee": null,
                "application_fee_amount": null,
                "balance_transaction": "txn_3OklVUJQC7CL5JsV0DjWFum2",
                "billing_details": {
                "address": {
                  "city": null,
                  "country": "DE",
                  "line1": null,
                  "line2": null,
                  "postal_code": null,
                  "state": null
                },
                "email": "hojuwymyj@mailinator.com",
                "name": "234234234234",
                "phone": null
                },
                "calculated_statement_descriptor": "Stripe",
                "captured": true,
                "created": 1708167152,
                "currency": "usd",
                "customer": "cus_PYa7B3aW0GR8Tp",
                "description": null,
                "destination": null,
                "dispute": null,
                "disputed": false,
                "failure_balance_transaction": null,
                "failure_code": null,
                "failure_message": null,
                "fraud_details": {
                },
                "invoice": null,
                "metadata": {
                  "order_uuid": "$orderUUID"
                },
                "on_behalf_of": null,
                "order": null,
                "outcome": {
                "network_status": "approved_by_network",
                "reason": null,
                "risk_level": "normal",
                "risk_score": 58,
                "seller_message": "Payment complete.",
                "type": "authorized"
                },
                "paid": true,
                "payment_method": "pm_1OjSxvJQC7CL5JsVXORPI3ow",
                "payment_method_details": {
                "card": {
                  "amount_authorized": 20000,
                  "brand": "visa",
                  "checks": {
                    "address_line1_check": null,
                    "address_postal_code_check": null,
                    "cvc_check": null
                  },
                  "country": "US",
                  "exp_month": 2,
                  "exp_year": 2034,
                  "extended_authorization": {
                    "status": "disabled"
                  },
                  "fingerprint": "FeBglJntP9jUtZ9s",
                  "funding": "credit",
                  "incremental_authorization": {
                    "status": "unavailable"
                  },
                  "installments": null,
                  "last4": "4242",
                  "mandate": null,
                  "multicapture": {
                    "status": "unavailable"
                  },
                  "network": "visa",
                  "network_token": {
                    "used": false
                  },
                  "overcapture": {
                    "maximum_amount_capturable": 20000,
                    "status": "unavailable"
                  },
                  "three_d_secure": null,
                  "wallet": null
                },
                "type": "card"
                },
                "radar_options": {
                },
                "receipt_email": null,
                "receipt_number": null,
                "receipt_url": "https://pay.stripe.com/receipts/payment/CAcaFwoVYWNjdF8xTmhlS1RKUUM3Q0w1SnNWKImFw64GMgaGkgN0CXs6LBae7nxE_zOpRx39z8SBysbBsikMWd5q6z2-0bOhKWvK-EUBZ0YkiI6NNXtn",
                "refunded": true,
                "review": null,
                "shipping": null,
                "source": null,
                "source_transfer": null,
                "statement_descriptor": null,
                "statement_descriptor_suffix": null,
                "transfer_data": null,
                "transfer_group": null
                }
          }
        }
JSON;

        return json_decode($json, true);
    }

    private function getStripeDispute(string $paymentIntentId)
    {
        $json = <<<JSON
        {
          "type": "charge.dispute.created",
          "id": "evt_1J5X2n2eZvKYlo2C0Q2Z2Z2Z",
          "object": "event",
          "api_version": "2020-08-27",
          "created": 1632830000,
          "data": {
            "object": {
              "id": "dp_1Okp9NJQC7CL5JsVnOrOgGuY",
              "object": "dispute",
              "livemode": false,
              "payment_intent": "$paymentIntentId",
              "status": "needs_response",
              "amount": 10000,
              "balance_transaction": "txn_1Okp9NJQC7CL5JsVHAyXzvYA",
              "balance_transactions": [
                {
                  "id": "txn_1Okp9NJQC7CL5JsVHAyXzvYA",
                  "object": "balance_transaction",
                  "amount": -9280,
                  "available_on": 1708732800,
                  "created": 1708181157,
                  "currency": "eur",
                  "description": "Chargeback withdrawal for ch_3Okp9LJQC7CL5JsV2LAHHPCi",
                  "exchange_rate": null,
                  "fee": 2000,
                  "fee_details": [
                    {
                      "amount": 2000,
                      "application": null,
                      "currency": "eur",
                      "description": "Dispute fee",
                      "type": "stripe_fee"
                    }
                  ],
                  "net": -11280,
                  "reporting_category": "dispute",
                  "source": "dp_1Okp9NJQC7CL5JsVnOrOgGuY",
                  "status": "pending",
                  "type": "adjustment"
                }
              ],
              "charge": "ch_3Okp9LJQC7CL5JsV2LAHHPCi",
              "created": 1708181157,
              "currency": "usd",
              "evidence": {
                "access_activity_log": null,
                "billing_address": "DE",
                "cancellation_policy": null,
                "cancellation_policy_disclosure": null,
                "cancellation_rebuttal": null,
                "customer_communication": null,
                "customer_email_address": "hojuwymyj@mailinator.com",
                "customer_name": "234234234234",
                "customer_purchase_ip": null,
                "customer_signature": null,
                "duplicate_charge_documentation": null,
                "duplicate_charge_explanation": null,
                "duplicate_charge_id": null,
                "product_description": null,
                "receipt": null,
                "refund_policy": null,
                "refund_policy_disclosure": null,
                "refund_refusal_explanation": null,
                "service_date": null,
                "service_documentation": null,
                "shipping_address": null,
                "shipping_carrier": null,
                "shipping_date": null,
                "shipping_documentation": null,
                "shipping_tracking_number": null,
                "uncategorized_file": null,
                "uncategorized_text": null
              },
              "evidence_details": {
                "due_by": 1708991999,
                "has_evidence": false,
                "past_due": false,
                "submission_count": 0
              },
              "is_charge_refundable": false,
              "metadata": {
              },
              "payment_method_details": {
                "card": {
                  "brand": "visa",
                  "network_reason_code": "83"
                },
                "type": "card"
              },
              "reason": "fraudulent"
            }
          }
        }

JSON;

        return json_decode($json, true);
    }

    private function getStripePaymentIntentSucceeded(string $paymentIntentId, string $orderUUID)
    {
        $json = <<<JSON
        {
          "type": "payment_intent.succeeded",
          "id": "evt_1J5X2n2eZvKYlo2C0Q2Z2Z2Z",
          "object": "event",
          "api_version": "2020-08-27",
          "created": 1632830000,
          "data": {
            "object": {
              "id": "$paymentIntentId",
              "object": "payment_intent",
              "last_payment_error": null,
              "livemode": false,
              "next_action": null,
              "status": "succeeded",
              "amount": 12600,
              "amount_capturable": 0,
              "amount_details": {
                "tip": {
                }
              },
              "amount_received": 12600,
              "application": null,
              "application_fee_amount": null,
              "automatic_payment_methods": null,
              "canceled_at": null,
              "cancellation_reason": null,
              "capture_method": "automatic",
              "client_secret": "pi_3Okqv0JQC7CL5JsV2lwbUtu6_secret_aNRpNbLaL1F63oFdS6GwNmnX8",
              "confirmation_method": "automatic",
              "created": 1708187954,
              "currency": "usd",
              "customer": "cus_PYa7B3aW0GR8Tp",
              "description": null,
              "invoice": null,
              "latest_charge": "ch_3Okqv0JQC7CL5JsV2z46y9CL",
              "metadata": {
                "order_uuid": "$orderUUID"
              },
              "on_behalf_of": null,
              "payment_method": "pm_1OjSxvJQC7CL5JsVXORPI3ow",
              "payment_method_configuration_details": null,
              "payment_method_options": {
                "card": {
                  "installments": null,
                  "mandate_options": null,
                  "network": null,
                  "request_three_d_secure": "automatic"
                }
              },
              "payment_method_types": [
                "card"
              ],
              "processing": null,
              "receipt_email": null,
              "review": null,
              "setup_future_usage": null,
              "shipping": null,
              "source": null,
              "statement_descriptor": null,
              "statement_descriptor_suffix": null,
              "transfer_data": null,
              "transfer_group": null
            }
          }
        }
JSON;

        return json_decode($json, true);
    }

    private function getStripePaymentIntentPaymentFailed(string $paymentIntentId, string $orderUUID)
    {
        $json = <<<JSON
        {
          "type": "payment_intent.payment_failed",
          "id": "evt_1J5X2n2eZvKYlo2C0Q2Z2Z2Z",
          "object": "event",
          "api_version": "2020-08-27",
          "created": 1632830000,
          "data": {
            "object": {
                  "id": "$paymentIntentId",
                  "object": "payment_intent",
                  "last_payment_error": {
                    "charge": "ch_3OlDhGJQC7CL5JsV0cQm0tC8",
                    "code": "card_declined",
                    "decline_code": "stolen_card",
                    "doc_url": "https://stripe.com/docs/error-codes/card-declined",
                    "message": "Your card was declined.",
                    "payment_method": {
                      "id": "pm_1OlDtOJQC7CL5JsV44LfwPNG",
                      "object": "payment_method",
                      "billing_details": {
                        "address": {
                          "city": null,
                          "country": "DE",
                          "line1": null,
                          "line2": null,
                          "postal_code": null,
                          "state": null
                        },
                        "email": "hojuwymyj@mailinator.com",
                        "name": "234234234234",
                        "phone": null
                      },
                      "card": {
                        "brand": "visa",
                        "checks": {
                          "address_line1_check": null,
                          "address_postal_code_check": null,
                          "cvc_check": "pass"
                        },
                        "country": "US",
                        "display_brand": "visa",
                        "exp_month": 3,
                        "exp_year": 2033,
                        "fingerprint": "AVcPfRlDRkMRXmJx",
                        "funding": "credit",
                        "generated_from": null,
                        "last4": "9979",
                        "networks": {
                          "available": [
                            "visa"
                          ],
                          "preferred": null
                        },
                        "three_d_secure_usage": {
                          "supported": true
                        },
                        "wallet": null
                      },
                      "created": 1708276266,
                      "customer": null,
                      "livemode": false,
                      "metadata": {
                      },
                      "type": "card"
                    },
                    "type": "card_error"
                  },
                  "livemode": false,
                  "next_action": null,
                  "status": "requires_payment_method",
                  "amount": 20000,
                  "amount_capturable": 0,
                  "amount_details": {
                    "tip": {
                    }
                  },
                  "amount_received": 0,
                  "application": null,
                  "application_fee_amount": null,
                  "automatic_payment_methods": null,
                  "canceled_at": null,
                  "cancellation_reason": null,
                  "capture_method": "automatic",
                  "client_secret": "pi_3OlDhGJQC7CL5JsV0c0a4vVU_secret_GEGMfr21myXAeeJPcrjLMExE4",
                  "confirmation_method": "automatic",
                  "created": 1708275514,
                  "currency": "usd",
                  "customer": "cus_PYa7B3aW0GR8Tp",
                  "description": null,
                  "invoice": null,
                  "latest_charge": "ch_3OlDhGJQC7CL5JsV0cQm0tC8",
                  "metadata": {
                    "order_uuid": "$orderUUID"
                  },
                  "on_behalf_of": null,
                  "payment_method": null,
                  "payment_method_configuration_details": null,
                  "payment_method_options": {
                    "card": {
                      "installments": null,
                      "mandate_options": null,
                      "network": null,
                      "request_three_d_secure": "automatic"
                    }
                  },
                  "payment_method_types": [
                    "card"
                  ],
                  "processing": null,
                  "receipt_email": null,
                  "review": null,
                  "setup_future_usage": null,
                  "shipping": null,
                  "source": null,
                  "statement_descriptor": null,
                  "statement_descriptor_suffix": null,
                  "transfer_data": null,
                  "transfer_group": null
                }
          }
        }
JSON;

        return json_decode($json, true);
    }

    private function getStripeInvoice(
        string $stripeInvoiceStatus,
        string $type,
        string $subscriptionUuid,
        string $invoiceId,
    ) {
        $json = <<<JSON
          {
          "type": "$type",
          "id": "evt_1J5X2n2eZvKYlo2C0Q2Z2Z2Z",
          "object": "event",
          "api_version": "2020-08-27",
          "created": 1632830000,
          "data": {
              "object": {
                "id": "$invoiceId",
                "object": "invoice",
                "account_country": "DE",
                "account_name": null,
                "account_tax_ids": null,
                "amount_due": 1100,
                "amount_paid": 0,
                "amount_remaining": 1100,
                "amount_shipping": 0,
                "application": null,
                "application_fee_amount": null,
                "attempt_count": 0,
                "attempted": false,
                "auto_advance": false,
                "automatic_tax": {
                  "enabled": false,
                  "status": null
                },
                "billing_reason": "subscription_create",
                "charge": null,
                "collection_method": "charge_automatically",
                "created": 1693839788,
                "currency": "usd",
                "custom_fields": null,
                "customer": "cus_OVg036aoJZPtXL",
                "customer_address": null,
                "customer_email": "test@gmail.com",
                "customer_name": "Booh",
                "customer_phone": null,
                "customer_shipping": null,
                "customer_tax_exempt": "none",
                "customer_tax_ids": [
                ],
                "default_payment_method": null,
                "default_source": null,
                "default_tax_rates": [
                ],
                "description": null,
                "discount": null,
                "discounts": [
                ],
                "due_date": null,
                "effective_at": 1693839788,
                "ending_balance": 0,
                "footer": null,
                "from_invoice": null,
                "hosted_invoice_url": "https://invoice.stripe.com/i/acct_1NheKTJQC7CL5JsV/test_YWNjdF8xTmhlS1RKUUM3Q0w1SnNWLF9PWm51R1JpYk1DODd1ZWZTTlg4NGE1Q1JVVDljT2JhLDg0MzgwNTkx0200Iscx1aj0?s=ap",
                "invoice_pdf": "https://pay.stripe.com/invoice/acct_1NheKTJQC7CL5JsV/test_YWNjdF8xTmhlS1RKUUM3Q0w1SnNWLF9PWm51R1JpYk1DODd1ZWZTTlg4NGE1Q1JVVDljT2JhLDg0MzgwNTkx0200Iscx1aj0/pdf?s=ap",
                "last_finalization_error": null,
                "latest_revision": null,
                "lines": {
                  "object": "list",
                  "data": [
                    {
                      "id": "il_1NmeIyJQC7CL5JsViY2FMHaT",
                      "object": "line_item",
                      "amount": 1100,
                      "amount_excluding_tax": 1100,
                      "currency": "usd",
                      "description": "1 × Monthly AI Images (at $11.00 / month)",
                      "discount_amounts": [
                      ],
                      "discountable": true,
                      "discounts": [
                      ],
                      "livemode": false,
                      "metadata": {
                        "subscription_uuid": "756fa6a0-0ab9-4023-b732-1b52e7d86e0e"
                      },
                      "period": {
                        "end": 1696431788,
                        "start": 1693839788
                      },
                      "plan": {
                        "id": "price_1NmeIvJQC7CL5JsVCJpxbS9L",
                        "object": "plan",
                        "active": false,
                        "aggregate_usage": null,
                        "amount": 1100,
                        "amount_decimal": "1100",
                        "billing_scheme": "per_unit",
                        "created": 1693839785,
                        "currency": "usd",
                        "interval": "month",
                        "interval_count": 1,
                        "livemode": false,
                        "metadata": {
                        },
                        "nickname": null,
                        "product": "sdf",
                        "tiers_mode": null,
                        "transform_usage": null,
                        "trial_period_days": null,
                        "usage_type": "licensed"
                      },
                      "price": {
                        "id": "price_1NmeIvJQC7CL5JsVCJpxbS9L",
                        "object": "price",
                        "active": false,
                        "billing_scheme": "per_unit",
                        "created": 1693839785,
                        "currency": "usd",
                        "custom_unit_amount": null,
                        "livemode": false,
                        "lookup_key": null,
                        "metadata": {
                        },
                        "nickname": null,
                        "product": "sdf",
                        "recurring": {
                          "aggregate_usage": null,
                          "interval": "month",
                          "interval_count": 1,
                          "trial_period_days": null,
                          "usage_type": "licensed"
                        },
                        "tax_behavior": "unspecified",
                        "tiers_mode": null,
                        "transform_quantity": null,
                        "type": "recurring",
                        "unit_amount": 1100,
                        "unit_amount_decimal": "1100"
                      },
                      "proration": false,
                      "proration_details": {
                        "credited_items": null
                      },
                      "quantity": 1,
                      "subscription": "sub_1NmeIyJQC7CL5JsVTf7ApLga",
                      "subscription_item": "si_OZnu0wgSk4Dzq1",
                      "tax_amounts": [
                      ],
                      "tax_rates": [
                      ],
                      "type": "subscription",
                      "unit_amount_excluding_tax": "1100"
                    }
                  ],
                  "has_more": false,
                  "total_count": 1,
                  "url": "/v1/invoices/in_1NmeIyJQC7CL5JsVsjFfiXIF/lines"
                },
                "livemode": false,
                "metadata": {
                },
                "next_payment_attempt": null,
                "number": "A10DDDFF-0004",
                "on_behalf_of": null,
                "paid": false,
                "paid_out_of_band": false,
                "payment_intent": "pi_3NmeIyJQC7CL5JsV0VXzUaBM",
                "payment_settings": {
                  "default_mandate": null,
                  "payment_method_options": null,
                  "payment_method_types": null
                },
                "period_end": 1693839788,
                "period_start": 1693839788,
                "post_payment_credit_notes_amount": 0,
                "pre_payment_credit_notes_amount": 0,
                "quote": null,
                "receipt_number": null,
                "rendering_options": null,
                "shipping_cost": null,
                "shipping_details": null,
                "starting_balance": 0,
                "statement_descriptor": null,
                "status": "$stripeInvoiceStatus",
                "status_transitions": {
                  "finalized_at": 1693839788,
                  "marked_uncollectible_at": null,
                  "paid_at": null,
                  "voided_at": null
                },
                "subscription": "sub_1NmeIyJQC7CL5JsVTf7ApLga",
                "subscription_details": {
                  "metadata": {
                    "subscription_uuid": "$subscriptionUuid"
                  }
                },
                "subtotal": 1100,
                "subtotal_excluding_tax": 1100,
                "tax": null,
                "test_clock": null,
                "total": 1100,
                "total_discount_amounts": [
                ],
                "total_excluding_tax": 1100,
                "total_tax_amounts": [
                ],
                "transfer_data": null,
                "webhooks_delivered_at": null
              }
            }
          }
JSON;

        return json_decode($json, true);
    }

    private function getStripeSubscription(
        string $stripeSubscriptionStatus,
        string $type,
        string $subscriptionUuid,
        int $quantity = 1,
    ) {
        $json = <<<JSON
        {
          "type": "$type",
          "id": "evt_1J5X2n2eZvKYlo2C0Q2Z2Z2Z",
          "object": "event",
          "api_version": "2020-08-27",
          "created": 1632830000,
          "data": {
              "object": {
                "id": "sub_1NnOIdJQC7CL5JsVPmRlNlsR",
                "object": "subscription",
                "currency": "usd",
                "canceled_at": null,
                "current_period_end": 1696608591,
                "current_period_start": 1694016591,
                "customer": "cus_OVg036aoJZPtXL",
                "ended_at": null,
                "items": {
                  "object": "list",
                  "data": [
                    {
                      "id": "si_OaZRwnUL1ruAJV",
                      "object": "subscription_item",
                      "billing_thresholds": null,
                      "created": 1694016591,
                      "metadata": {
                        "subscription_uuid": "756fa6a0-0ab9-4023-b732-1b52e7d86e0e"
                      },
                      "plan": {
                        "id": "price_1NnOIaJQC7CL5JsVT9G8URTq",
                        "object": "plan",
                        "active": false,
                        "aggregate_usage": null,
                        "amount": 1100,
                        "amount_decimal": "1100",
                        "billing_scheme": "per_unit",
                        "created": 1694016588,
                        "currency": "usd",
                        "interval": "month",
                        "interval_count": 1,
                        "livemode": false,
                        "metadata": {
                        },
                        "nickname": null,
                        "product": "sdf",
                        "tiers_mode": null,
                        "transform_usage": null,
                        "trial_period_days": null,
                        "usage_type": "licensed"
                      },
                      "price": {
                        "id": "price_1NnOIaJQC7CL5JsVT9G8URTq",
                        "object": "price",
                        "active": false,
                        "billing_scheme": "per_unit",
                        "created": 1694016588,
                        "currency": "usd",
                        "custom_unit_amount": null,
                        "livemode": false,
                        "lookup_key": null,
                        "metadata": {
                        },
                        "nickname": null,
                        "product": "sdf",
                        "recurring": {
                          "aggregate_usage": null,
                          "interval": "month",
                          "interval_count": 1,
                          "trial_period_days": null,
                          "usage_type": "licensed"
                        },
                        "tax_behavior": "unspecified",
                        "tiers_mode": null,
                        "transform_quantity": null,
                        "type": "recurring",
                        "unit_amount": 1100,
                        "unit_amount_decimal": "1100"
                      },
                      "quantity": $quantity,
                      "subscription": "sub_1NnOIdJQC7CL5JsVPmRlNlsR",
                      "tax_rates": [
                      ]
                    }
                  ],
                  "has_more": false,
                  "total_count": 1,
                  "url": "/v1/subscription_items?subscription=sub_1NnOIdJQC7CL5JsVPmRlNlsR"
                },
                "latest_invoice": "in_1NnOIdJQC7CL5JsVMDKMZOlI",
                "livemode": false,
                "metadata": {
                  "subscription_uuid": "$subscriptionUuid"
                },
                "next_pending_invoice_item_invoice": null,
                "on_behalf_of": null,
                "pause_collection": null,
                "payment_settings": {
                  "payment_method_options": null,
                  "payment_method_types": null,
                  "save_default_payment_method": "off"
                },
                "pending_invoice_item_interval": null,
                "pending_setup_intent": null,
                "pending_update": null,
                "plan": {
                  "id": "price_1NnOIaJQC7CL5JsVT9G8URTq",
                  "object": "plan",
                  "active": false,
                  "aggregate_usage": null,
                  "amount": 1100,
                  "amount_decimal": "1100",
                  "billing_scheme": "per_unit",
                  "created": 1694016588,
                  "currency": "usd",
                  "interval": "month",
                  "interval_count": 1,
                  "livemode": false,
                  "metadata": {
                  },
                  "nickname": null,
                  "product": "sdf",
                  "tiers_mode": null,
                  "transform_usage": null,
                  "trial_period_days": null,
                  "usage_type": "licensed"
                },
                "quantity": $quantity,
                "schedule": null,
                "start_date": 1694016591,
                "status": "$stripeSubscriptionStatus",
                "test_clock": null,
                "transfer_data": null,
                "trial_end": null,
                "trial_settings": {
                  "end_behavior": {
                    "missing_payment_method": "create_invoice"
                  }
                },
                "trial_start": null
              }
            }
          }
JSON;

        return json_decode($json, true);
    }
}
