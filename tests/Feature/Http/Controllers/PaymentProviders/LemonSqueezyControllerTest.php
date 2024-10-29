<?php

namespace Tests\Feature\Http\Controllers\PaymentProviders;

use App\Constants\OrderStatus;
use App\Constants\PaymentProviderConstants;
use App\Constants\SubscriptionStatus;
use App\Constants\TransactionStatus;
use App\Models\Currency;
use App\Models\OneTimeProduct;
use App\Models\OneTimeProductPaymentProviderData;
use App\Models\Order;
use App\Models\PaymentProvider;
use App\Models\Plan;
use App\Models\PlanPaymentProviderData;
use App\Models\Subscription;
use Illuminate\Support\Str;
use Tests\Feature\FeatureTest;

class LemonSqueezyControllerTest extends FeatureTest
{
    public function test_webhook_with_no_signature(): void
    {
        $response = $this->postJson(route('payments-providers.lemon-squeezy.webhook'), [], [
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(400);
    }

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
            'interval_count' => 2,
            'status' => SubscriptionStatus::NEW->value,
        ]);

        $payload = $this->getLemonSqueezySubscriptionEvent('active', 'subscription_created', $uuid, '309911');

        $signature = $this->generateSignature(json_encode($payload));

        $response = $this->postJson(route('payments-providers.lemon-squeezy.webhook'), $payload, [
            'X-Signature' => $signature,
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('subscriptions', [
            'uuid' => $uuid,
            'status' => SubscriptionStatus::ACTIVE->value,
        ]);
    }

    public function test_subscription_created_without_subscription_webhook(): void
    {
        $variantId = '3030';
        $plan = Plan::factory()->create([
            'name' => 'Test Plan',
            'slug' => 'test-plan',
            'is_active' => true,
        ]);

        $plan->prices()->create([
            'price' => 10,
            'currency_id' => Currency::where('code', 'USD')->firstOrFail()->id,
        ]);

        PlanPaymentProviderData::create([
            'plan_id' => $plan->id,
            'payment_provider_id' => PaymentProvider::where('slug', PaymentProviderConstants::LEMON_SQUEEZY_SLUG)->firstOrFail()->id,
            'payment_provider_product_id' => $variantId,
        ]);

        $providerSubscriptionId = '309931';

        $payload = $this->getLemonSqueezySubscriptionEventWithNoMetaData('active', 'subscription_created', $providerSubscriptionId, $variantId);

        $signature = $this->generateSignature(json_encode($payload));

        $response = $this->postJson(route('payments-providers.lemon-squeezy.webhook'), $payload, [
            'X-Signature' => $signature,
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('subscriptions', [
            'payment_provider_subscription_id' => $providerSubscriptionId,
            'status' => SubscriptionStatus::ACTIVE->value,
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
            'interval_count' => 2,
            'payment_provider_id' => PaymentProvider::where('slug', PaymentProviderConstants::LEMON_SQUEEZY_SLUG)->firstOrFail()->id,
            'payment_provider_subscription_id' => '309912',
            'status' => SubscriptionStatus::ACTIVE->value,
            'quantity' => 1,
        ]);

        $payload = $this->getLemonSqueezySubscriptionEvent('expired', 'subscription_updated', $uuid, '309912', quantity: 2);

        $signature = $this->generateSignature(json_encode($payload));

        $response = $this->postJson(route('payments-providers.lemon-squeezy.webhook'), $payload, [
            'X-Signature' => $signature,
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('subscriptions', [
            'uuid' => $uuid,
            'status' => SubscriptionStatus::INACTIVE->value,
            'quantity' => 2,
        ]);
    }

    public function test_subscription_canceled_webhook(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);

        $uuid = (string) Str::uuid();
        $providerSubscriptionId = '309913';
        Subscription::create([
            'uuid' => $uuid,
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'price' => 10,
            'currency_id' => 1,
            'plan_id' => 1,
            'interval_id' => 2,
            'interval_count' => 2,
            'payment_provider_id' => PaymentProvider::where('slug', PaymentProviderConstants::LEMON_SQUEEZY_SLUG)->firstOrFail()->id,
            'payment_provider_subscription_id' => $providerSubscriptionId,
            'status' => SubscriptionStatus::ACTIVE->value,
        ]);

        $payload = $this->getLemonSqueezySubscriptionEvent('cancelled', 'subscription_updated', $uuid, $providerSubscriptionId, '"2024-03-19T15:08:45.000000Z"', 'true');

        $signature = $this->generateSignature(json_encode($payload));

        $response = $this->postJson(route('payments-providers.lemon-squeezy.webhook'), $payload, [
            'X-Signature' => $signature,
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('subscriptions', [
            'uuid' => $uuid,
            'status' => SubscriptionStatus::ACTIVE->value,
            'is_canceled_at_end_of_cycle' => true,
        ]);
    }

    public function test_subscription_payment_success_webhook(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);

        $uuid = (string) Str::uuid();
        $providerSubscriptionId = '309914';
        $subscription = Subscription::create([
            'uuid' => $uuid,
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'price' => 10,
            'currency_id' => 1,
            'plan_id' => 1,
            'interval_id' => 2,
            'interval_count' => 2,
            'payment_provider_id' => PaymentProvider::where('slug', PaymentProviderConstants::LEMON_SQUEEZY_SLUG)->firstOrFail()->id,
            'payment_provider_subscription_id' => $providerSubscriptionId,
            'status' => SubscriptionStatus::ACTIVE->value,
        ]);

        $payload = $this->getLemonSqueezySubscriptionInvoicesEvent('subscription_payment_success', '1234', $providerSubscriptionId);

        $signature = $this->generateSignature(json_encode($payload));

        $response = $this->postJson(route('payments-providers.lemon-squeezy.webhook'), $payload, [
            'X-Signature' => $signature,
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'subscription_id' => $subscription->id,
            'status' => TransactionStatus::SUCCESS->value,
        ]);
    }

    public function test_subscription_payment_failed_webhook(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);

        $uuid = (string) Str::uuid();
        $providerSubscriptionId = '309916';
        $subscription = Subscription::create([
            'uuid' => $uuid,
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'price' => 10,
            'currency_id' => 1,
            'plan_id' => 1,
            'interval_id' => 2,
            'interval_count' => 2,
            'payment_provider_id' => PaymentProvider::where('slug', PaymentProviderConstants::LEMON_SQUEEZY_SLUG)->firstOrFail()->id,
            'payment_provider_subscription_id' => $providerSubscriptionId,
            'status' => SubscriptionStatus::ACTIVE->value,
        ]);

        $payload = $this->getLemonSqueezySubscriptionInvoicesEvent('subscription_payment_failed', '1235', $providerSubscriptionId, 'failed');

        $signature = $this->generateSignature(json_encode($payload));

        $response = $this->postJson(route('payments-providers.lemon-squeezy.webhook'), $payload, [
            'X-Signature' => $signature,
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'subscription_id' => $subscription->id,
            'status' => TransactionStatus::FAILED->value,
        ]);
    }

    public function test_order_created_webhook(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);

        $uuid = (string) Str::uuid();
        Order::create([
            'uuid' => $uuid,
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'total_amount' => 10,
            'currency_id' => 1,
            'status' => OrderStatus::NEW->value,
        ]);

        $payload = $this->getLemonSqueezyOrderEvent('order_created', '309970', $uuid, 'paid');

        $signature = $this->generateSignature(json_encode($payload));

        $response = $this->postJson(route('payments-providers.lemon-squeezy.webhook'), $payload, [
            'X-Signature' => $signature,
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'uuid' => $uuid,
            'status' => OrderStatus::SUCCESS->value,
        ]);

        $this->assertDatabaseHas('transactions', [
            'order_id' => Order::where('uuid', $uuid)->firstOrFail()->id,
            'status' => TransactionStatus::SUCCESS->value,
        ]);
    }

    public function test_order_created_without_order_webhook(): void
    {
        $variantId = '3032';
        $orderProviderId = '309975';
        $product = OneTimeProduct::factory()->create([
            'name' => 'Test Plan',
            'slug' => 'test-plan',
            'is_active' => true,
            'metadata' => [],
            'features' => [],
        ]);

        $product->prices()->create([
            'price' => 10,
            'currency_id' => Currency::where('code', 'USD')->firstOrFail()->id,
        ]);

        OneTimeProductPaymentProviderData::create([
            'one_time_product_id' => $product->id,
            'payment_provider_id' => PaymentProvider::where('slug', PaymentProviderConstants::LEMON_SQUEEZY_SLUG)->firstOrFail()->id,
            'payment_provider_product_id' => $variantId,
        ]);

        $payload = $this->getLemonSqueezyOrderEventWithoutMetadata('order_created', $orderProviderId, 'paid', '3032');

        $signature = $this->generateSignature(json_encode($payload));

        $response = $this->postJson(route('payments-providers.lemon-squeezy.webhook'), $payload, [
            'X-Signature' => $signature,
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'status' => OrderStatus::SUCCESS->value,
            'payment_provider_id' => PaymentProvider::where('slug', PaymentProviderConstants::LEMON_SQUEEZY_SLUG)->firstOrFail()->id,
            'payment_provider_order_id' => $orderProviderId,
        ]);

        $this->assertDatabaseHas('transactions', [
            'payment_provider_id' => PaymentProvider::where('slug', PaymentProviderConstants::LEMON_SQUEEZY_SLUG)->firstOrFail()->id,
            'payment_provider_transaction_id' => $orderProviderId,
            'status' => TransactionStatus::SUCCESS->value,
        ]);
    }

    public function test_order_refunded_webhook(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);

        $uuid = (string) Str::uuid();
        Order::create([
            'uuid' => $uuid,
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'total_amount' => 10,
            'currency_id' => 1,
            'status' => OrderStatus::SUCCESS->value,
        ]);

        $payload = $this->getLemonSqueezyOrderEvent('order_refunded', '309971', $uuid, 'refunded');

        $signature = $this->generateSignature(json_encode($payload));

        $response = $this->postJson(route('payments-providers.lemon-squeezy.webhook'), $payload, [
            'X-Signature' => $signature,
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'uuid' => $uuid,
            'status' => OrderStatus::REFUNDED->value,
        ]);

        $this->assertDatabaseHas('transactions', [
            'payment_provider_id' => PaymentProvider::where('slug', PaymentProviderConstants::LEMON_SQUEEZY_SLUG)->firstOrFail()->id,
            'payment_provider_transaction_id' => '309971',
            'status' => TransactionStatus::REFUNDED->value,
        ]);
    }


    private function generateSignature(string $content)
    {
        $secret = config('services.lemon-squeezy.signing_secret');

        return hash_hmac('sha256', $content, $secret);
    }

    private function getLemonSqueezySubscriptionEvent(
        string $status,
        string $type,
        string $subscriptionUuid,
        string $providerSubscriptionId,
        string $endsAt = 'null',
        string $cancelled = 'false',
        int $quantity = 1,
    ) {
        $json = <<<JSON
        {
            "meta": {
                "test_mode": true,
                "event_name": "$type",
                "custom_data": {
                    "subscription_uuid": "$subscriptionUuid"
                },
                "webhook_id": "cb70b2c7-36fa-44ae-bc58-925d3e3e3e3e"
            },
            "data": {
                "type": "subscriptions",
                "id": "$providerSubscriptionId",
                "attributes": {
                    "store_id": 61561,
                    "customer_id": 2470299,
                    "order_id": 2323439,
                    "order_item_id": 2284278,
                    "product_id": 219808,
                    "variant_id": 299710,
                    "product_name": "Pro",
                    "variant_name": "Pro Yearly",
                    "user_name": "John Doe",
                    "user_email": "john.doe@doe.com",
                    "status": "$status",
                    "status_formatted": "Active",
                    "card_brand": "visa",
                    "card_last_four": "4242",
                    "pause": null,
                    "cancelled": $cancelled,
                    "trial_ends_at": null,
                    "billing_anchor": 19,
                    "first_subscription_item": {
                        "id": 257408,
                        "subscription_id": 309912,
                        "price_id": 407140,
                        "quantity": $quantity,
                        "is_usage_based": false,
                        "created_at": "2024-03-19T15:08:45.000000Z",
                        "updated_at": "2024-03-19T16:26:36.000000Z"
                    },
                    "urls": {
                        "update_payment_method": "https://x.lemonsqueezy.com/subscription/309912/payment-details?expires=1710951996&signature=9596fa98bebdfd35c6ea463ebbe4ce4f252bb9c8d5c67b70611c77bc5bb28489",
                        "customer_portal": "https://x.lemonsqueezy.com/billing?expires=1710887196&test_mode=1&user=1798461&signature=97f7af53d2e0eedac429c172b3f79e5305f9e083867a5e031d0ce95ad7993622",
                        "customer_portal_update_subscription": "https://x.lemonsqueezy.com/billing/309912/update?expires=1710951996&user=1798461&signature=21a8f96ec278eb7c48abdc9190cc58f2cb9750312fdffdd45bd61acb9cc3d224"
                    },
                    "renews_at": "2025-03-19T15:08:39.000000Z",
                    "ends_at": $endsAt,
                    "created_at": "2024-03-19T15:08:40.000000Z",
                    "updated_at": "2024-03-19T16:26:35.000000Z",
                    "test_mode": true
                },
                "relationships": {
                    "store": {
                        "links": {
                            "related": "https://api.lemonsqueezy.com/v1/subscriptions/309912/store",
                            "self": "https://api.lemonsqueezy.com/v1/subscriptions/309912/relationships/store"
                        }
                    },
                    "customer": {
                        "links": {
                            "related": "https://api.lemonsqueezy.com/v1/subscriptions/309912/customer",
                            "self": "https://api.lemonsqueezy.com/v1/subscriptions/309912/relationships/customer"
                        }
                    },
                    "order": {
                        "links": {
                            "related": "https://api.lemonsqueezy.com/v1/subscriptions/309912/order",
                            "self": "https://api.lemonsqueezy.com/v1/subscriptions/309912/relationships/order"
                        }
                    },
                    "order-item": {
                        "links": {
                            "related": "https://api.lemonsqueezy.com/v1/subscriptions/309912/order-item",
                            "self": "https://api.lemonsqueezy.com/v1/subscriptions/309912/relationships/order-item"
                        }
                    },
                    "product": {
                        "links": {
                            "related": "https://api.lemonsqueezy.com/v1/subscriptions/309912/product",
                            "self": "https://api.lemonsqueezy.com/v1/subscriptions/309912/relationships/product"
                        }
                    },
                    "variant": {
                        "links": {
                            "related": "https://api.lemonsqueezy.com/v1/subscriptions/309912/variant",
                            "self": "https://api.lemonsqueezy.com/v1/subscriptions/309912/relationships/variant"
                        }
                    },
                    "subscription-items": {
                        "links": {
                            "related": "https://api.lemonsqueezy.com/v1/subscriptions/309912/subscription-items",
                            "self": "https://api.lemonsqueezy.com/v1/subscriptions/309912/relationships/subscription-items"
                        }
                    },
                    "subscription-invoices": {
                        "links": {
                            "related": "https://api.lemonsqueezy.com/v1/subscriptions/309912/subscription-invoices",
                            "self": "https://api.lemonsqueezy.com/v1/subscriptions/309912/relationships/subscription-invoices"
                        }
                    }
                },
                "links": {
                    "self": "https://api.lemonsqueezy.com/v1/subscriptions/309912"
                }
            }
        }
JSON;

        return json_decode($json, true);
    }

    private function getLemonSqueezySubscriptionEventWithNoMetaData(
        string $status,
        string $type,
        string $providerSubscriptionId,
        string $variantId,
        string $endsAt = 'null',
        string $cancelled = 'false'
    ) {
        $json = <<<JSON
        {
            "meta": {
                "test_mode": true,
                "event_name": "$type",
                "webhook_id": "cb70b2c7-36fa-44ae-bc58-925d3e3e3e3e"
            },
            "data": {
                "type": "subscriptions",
                "id": "$providerSubscriptionId",
                "attributes": {
                    "store_id": 61561,
                    "customer_id": 2470299,
                    "order_id": 2323439,
                    "order_item_id": 2284278,
                    "product_id": 219808,
                    "variant_id": $variantId,
                    "product_name": "Pro",
                    "variant_name": "Pro Yearly",
                    "user_name": "John Doe",
                    "user_email": "john.doe@doe.com",
                    "status": "$status",
                    "status_formatted": "Active",
                    "card_brand": "visa",
                    "card_last_four": "4242",
                    "pause": null,
                    "cancelled": $cancelled,
                    "trial_ends_at": null,
                    "billing_anchor": 19,
                    "first_subscription_item": {
                        "id": 257408,
                        "subscription_id": 309912,
                        "price_id": 407140,
                        "quantity": 1,
                        "is_usage_based": false,
                        "created_at": "2024-03-19T15:08:45.000000Z",
                        "updated_at": "2024-03-19T16:26:36.000000Z"
                    },
                    "urls": {
                        "update_payment_method": "https://x.lemonsqueezy.com/subscription/309912/payment-details?expires=1710951996&signature=9596fa98bebdfd35c6ea463ebbe4ce4f252bb9c8d5c67b70611c77bc5bb28489",
                        "customer_portal": "https://x.lemonsqueezy.com/billing?expires=1710887196&test_mode=1&user=1798461&signature=97f7af53d2e0eedac429c172b3f79e5305f9e083867a5e031d0ce95ad7993622",
                        "customer_portal_update_subscription": "https://x.lemonsqueezy.com/billing/309912/update?expires=1710951996&user=1798461&signature=21a8f96ec278eb7c48abdc9190cc58f2cb9750312fdffdd45bd61acb9cc3d224"
                    },
                    "renews_at": "2025-03-19T15:08:39.000000Z",
                    "ends_at": $endsAt,
                    "created_at": "2024-03-19T15:08:40.000000Z",
                    "updated_at": "2024-03-19T16:26:35.000000Z",
                    "test_mode": true
                },
                "relationships": {
                    "store": {
                        "links": {
                            "related": "https://api.lemonsqueezy.com/v1/subscriptions/309912/store",
                            "self": "https://api.lemonsqueezy.com/v1/subscriptions/309912/relationships/store"
                        }
                    },
                    "customer": {
                        "links": {
                            "related": "https://api.lemonsqueezy.com/v1/subscriptions/309912/customer",
                            "self": "https://api.lemonsqueezy.com/v1/subscriptions/309912/relationships/customer"
                        }
                    },
                    "order": {
                        "links": {
                            "related": "https://api.lemonsqueezy.com/v1/subscriptions/309912/order",
                            "self": "https://api.lemonsqueezy.com/v1/subscriptions/309912/relationships/order"
                        }
                    },
                    "order-item": {
                        "links": {
                            "related": "https://api.lemonsqueezy.com/v1/subscriptions/309912/order-item",
                            "self": "https://api.lemonsqueezy.com/v1/subscriptions/309912/relationships/order-item"
                        }
                    },
                    "product": {
                        "links": {
                            "related": "https://api.lemonsqueezy.com/v1/subscriptions/309912/product",
                            "self": "https://api.lemonsqueezy.com/v1/subscriptions/309912/relationships/product"
                        }
                    },
                    "variant": {
                        "links": {
                            "related": "https://api.lemonsqueezy.com/v1/subscriptions/309912/variant",
                            "self": "https://api.lemonsqueezy.com/v1/subscriptions/309912/relationships/variant"
                        }
                    },
                    "subscription-items": {
                        "links": {
                            "related": "https://api.lemonsqueezy.com/v1/subscriptions/309912/subscription-items",
                            "self": "https://api.lemonsqueezy.com/v1/subscriptions/309912/relationships/subscription-items"
                        }
                    },
                    "subscription-invoices": {
                        "links": {
                            "related": "https://api.lemonsqueezy.com/v1/subscriptions/309912/subscription-invoices",
                            "self": "https://api.lemonsqueezy.com/v1/subscriptions/309912/relationships/subscription-invoices"
                        }
                    }
                },
                "links": {
                    "self": "https://api.lemonsqueezy.com/v1/subscriptions/309912"
                }
            }
        }
JSON;

        return json_decode($json, true);
    }

    private function getLemonSqueezySubscriptionInvoicesEvent(
        string $type,
        string $providerId,
        string $subscriptionProviderId,
        string $status = 'paid'
    ) {
        $json = <<<JSON
        {
          "data": {
            "id": "$providerId",
            "type": "subscription-invoices",
            "links": {
              "self": "https://api.lemonsqueezy.com/v1/subscription-invoices/770140"
            },
            "attributes": {
              "tax": 0,
              "urls": {
                "invoice_url": "https://app.lemonsqueezy.com/my-orders/29e836fd-6b03-4d17-8854-37c55131280b/subscription-invoice/770140?signature=be26e649499ab4b53faffbd11fa7a6e2d5bdfcbc6b12fe0e391119b7946e14d1"
              },
              "total": 0,
              "status": "$status",
              "tax_usd": 0,
              "currency": "USD",
              "refunded": false,
              "store_id": 61561,
              "subtotal": -8498,
              "test_mode": true,
              "total_usd": 0,
              "user_name": "John Doe",
              "card_brand": null,
              "created_at": "2024-03-19T16:31:36.000000Z",
              "updated_at": "2024-03-19T16:31:37.000000Z",
              "user_email": "john@doe.com",
              "customer_id": 2470299,
              "refunded_at": null,
              "subtotal_usd": 8498,
              "currency_rate": "1.00000000",
              "tax_formatted": "$0.00",
              "tax_inclusive": false,
              "billing_reason": "updated",
              "card_last_four": null,
              "discount_total": 0,
              "subscription_id": $subscriptionProviderId,
              "total_formatted": "$0.00",
              "status_formatted": "Paid",
              "discount_total_usd": 0,
              "subtotal_formatted": "$84.98",
              "discount_total_formatted": "$0.00"
            },
            "relationships": {
              "store": {
                "links": {
                  "self": "https://api.lemonsqueezy.com/v1/subscription-invoices/770140/relationships/store",
                  "related": "https://api.lemonsqueezy.com/v1/subscription-invoices/770140/store"
                }
              },
              "customer": {
                "links": {
                  "self": "https://api.lemonsqueezy.com/v1/subscription-invoices/770140/relationships/customer",
                  "related": "https://api.lemonsqueezy.com/v1/subscription-invoices/770140/customer"
                }
              },
              "subscription": {
                "links": {
                  "self": "https://api.lemonsqueezy.com/v1/subscription-invoices/770140/relationships/subscription",
                  "related": "https://api.lemonsqueezy.com/v1/subscription-invoices/770140/subscription"
                }
              }
            }
          },
          "meta": {
            "test_mode": true,
            "event_name": "$type",
            "webhook_id": "cafa4922-5aec-4681-8254-c5fac103df1f",
            "custom_data": {
              "subscription_uuid": "f04893fa-e203-4f09-99a1-f2d0425532d7"
            }
          }
        }
JSON;

        return json_decode($json, true);
    }

    private function getLemonSqueezyOrderEvent(
        string $type,
        string $orderProviderId,
        string $orderUuid,
        string $status = 'paid'
    ) {
        $json = <<<JSON
        {
          "data": {
            "id": "$orderProviderId",
            "type": "orders",
            "links": {
              "self": "https://api.lemonsqueezy.com/v1/orders/2322144"
            },
            "attributes": {
              "tax": 0,
              "urls": {
                "receipt": "https://app.lemonsqueezy.com/my-orders/a9e0edf5-d5fc-46f9-93b8-92284abfe75e?signature=c0f3699c90519aec2847ca6943ef657c7fbf052b054e97d0ec81399512e9c91a"
              },
              "total": 11000,
              "status": "$status",
              "tax_usd": 0,
              "currency": "USD",
              "refunded": false,
              "store_id": 61561,
              "subtotal": 11000,
              "tax_name": "",
              "tax_rate": 0,
              "setup_fee": 0,
              "test_mode": true,
              "total_usd": 11000,
              "user_name": "John Doe",
              "created_at": "2024-03-19T11:17:48.000000Z",
              "identifier": "a9e0edf5-d5fc-46f9-93b8-92284abfe75e",
              "updated_at": "2024-03-19T11:17:48.000000Z",
              "user_email": "john@doe.com",
              "customer_id": 2470299,
              "refunded_at": null,
              "order_number": 6156122,
              "subtotal_usd": 11000,
              "currency_rate": "1.00000000",
              "setup_fee_usd": 0,
              "tax_formatted": "$0.00",
              "tax_inclusive": false,
              "discount_total": 0,
              "total_formatted": "$110.00",
              "first_order_item": {
                "id": 2282986,
                "price": 11000,
                "order_id": 2322144,
                "price_id": 388424,
                "quantity": 1,
                "test_mode": true,
                "created_at": "2024-03-19T11:17:48.000000Z",
                "product_id": 211951,
                "updated_at": "2024-03-19T11:17:48.000000Z",
                "variant_id": 286458,
                "product_name": "Basic",
                "variant_name": "Basic"
              },
              "status_formatted": "Paid",
              "discount_total_usd": 0,
              "subtotal_formatted": "$110.00",
              "setup_fee_formatted": "$0.00",
              "discount_total_formatted": "$0.00"
            },
            "relationships": {
              "store": {
                "links": {
                  "self": "https://api.lemonsqueezy.com/v1/orders/2322144/relationships/store",
                  "related": "https://api.lemonsqueezy.com/v1/orders/2322144/store"
                }
              },
              "customer": {
                "links": {
                  "self": "https://api.lemonsqueezy.com/v1/orders/2322144/relationships/customer",
                  "related": "https://api.lemonsqueezy.com/v1/orders/2322144/customer"
                }
              },
              "order-items": {
                "links": {
                  "self": "https://api.lemonsqueezy.com/v1/orders/2322144/relationships/order-items",
                  "related": "https://api.lemonsqueezy.com/v1/orders/2322144/order-items"
                }
              },
              "license-keys": {
                "links": {
                  "self": "https://api.lemonsqueezy.com/v1/orders/2322144/relationships/license-keys",
                  "related": "https://api.lemonsqueezy.com/v1/orders/2322144/license-keys"
                }
              },
              "subscriptions": {
                "links": {
                  "self": "https://api.lemonsqueezy.com/v1/orders/2322144/relationships/subscriptions",
                  "related": "https://api.lemonsqueezy.com/v1/orders/2322144/subscriptions"
                }
              },
              "discount-redemptions": {
                "links": {
                  "self": "https://api.lemonsqueezy.com/v1/orders/2322144/relationships/discount-redemptions",
                  "related": "https://api.lemonsqueezy.com/v1/orders/2322144/discount-redemptions"
                }
              }
            }
          },
          "meta": {
            "test_mode": true,
            "event_name": "$type",
            "webhook_id": "da79a591-282e-44b8-a0e6-0593dcac2e35",
            "custom_data": {
              "order_uuid": "$orderUuid"
            }
          }
        }
JSON;

        return json_decode($json, true);
    }

    private function getLemonSqueezyOrderEventWithoutMetadata(
        string $type,
        string $orderProviderId,
        string $status = 'paid',
        string $variantId = '286458'
    ) {
        $json = <<<JSON
        {
          "data": {
            "id": "$orderProviderId",
            "type": "orders",
            "links": {
              "self": "https://api.lemonsqueezy.com/v1/orders/2322144"
            },
            "attributes": {
              "tax": 0,
              "urls": {
                "receipt": "https://app.lemonsqueezy.com/my-orders/a9e0edf5-d5fc-46f9-93b8-92284abfe75e?signature=c0f3699c90519aec2847ca6943ef657c7fbf052b054e97d0ec81399512e9c91a"
              },
              "total": 11000,
              "status": "$status",
              "tax_usd": 0,
              "currency": "USD",
              "refunded": false,
              "store_id": 61561,
              "subtotal": 11000,
              "tax_name": "",
              "tax_rate": 0,
              "setup_fee": 0,
              "test_mode": true,
              "total_usd": 11000,
              "user_name": "John Doe",
              "created_at": "2024-03-19T11:17:48.000000Z",
              "identifier": "a9e0edf5-d5fc-46f9-93b8-92284abfe75e",
              "updated_at": "2024-03-19T11:17:48.000000Z",
              "user_email": "john@doe.com",
              "customer_id": 2470299,
              "refunded_at": null,
              "order_number": 6156122,
              "subtotal_usd": 11000,
              "currency_rate": "1.00000000",
              "setup_fee_usd": 0,
              "tax_formatted": "$0.00",
              "tax_inclusive": false,
              "discount_total": 0,
              "total_formatted": "$110.00",
              "first_order_item": {
                "id": 2282986,
                "price": 11000,
                "order_id": 2322144,
                "price_id": 388424,
                "quantity": 1,
                "test_mode": true,
                "created_at": "2024-03-19T11:17:48.000000Z",
                "product_id": 211951,
                "updated_at": "2024-03-19T11:17:48.000000Z",
                "variant_id": $variantId,
                "product_name": "Basic",
                "variant_name": "Basic"
              },
              "status_formatted": "Paid",
              "discount_total_usd": 0,
              "subtotal_formatted": "$110.00",
              "setup_fee_formatted": "$0.00",
              "discount_total_formatted": "$0.00"
            },
            "relationships": {
              "store": {
                "links": {
                  "self": "https://api.lemonsqueezy.com/v1/orders/2322144/relationships/store",
                  "related": "https://api.lemonsqueezy.com/v1/orders/2322144/store"
                }
              },
              "customer": {
                "links": {
                  "self": "https://api.lemonsqueezy.com/v1/orders/2322144/relationships/customer",
                  "related": "https://api.lemonsqueezy.com/v1/orders/2322144/customer"
                }
              },
              "order-items": {
                "links": {
                  "self": "https://api.lemonsqueezy.com/v1/orders/2322144/relationships/order-items",
                  "related": "https://api.lemonsqueezy.com/v1/orders/2322144/order-items"
                }
              },
              "license-keys": {
                "links": {
                  "self": "https://api.lemonsqueezy.com/v1/orders/2322144/relationships/license-keys",
                  "related": "https://api.lemonsqueezy.com/v1/orders/2322144/license-keys"
                }
              },
              "subscriptions": {
                "links": {
                  "self": "https://api.lemonsqueezy.com/v1/orders/2322144/relationships/subscriptions",
                  "related": "https://api.lemonsqueezy.com/v1/orders/2322144/subscriptions"
                }
              },
              "discount-redemptions": {
                "links": {
                  "self": "https://api.lemonsqueezy.com/v1/orders/2322144/relationships/discount-redemptions",
                  "related": "https://api.lemonsqueezy.com/v1/orders/2322144/discount-redemptions"
                }
              }
            }
          },
          "meta": {
            "test_mode": true,
            "event_name": "$type",
            "webhook_id": "da79a591-282e-44b8-a0e6-0593dcac2e35"
          }
        }
JSON;

        return json_decode($json, true);
    }
}
