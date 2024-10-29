<?php

namespace Tests\Feature\Http\Controllers;

use App\Constants\OrderStatus;
use App\Constants\TransactionStatus;
use App\Models\Currency;
use App\Models\OneTimeProduct;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\Feature\FeatureTest;

class InvoiceControllerTest extends FeatureTest
{
    public function test_generate()
    {
        config(['invoices.enabled' => true]);

        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);

        $product = OneTimeProduct::factory()->create([
            'slug' => 'product-slug-'.Str::random(20),
            'is_active' => true,
        ]);

        $order = Order::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => 1,
            'tenant_id' => $tenant->id,
            'total_amount' => 10,
            'currency_id' => 1,
            'status' => OrderStatus::SUCCESS->value,
        ]);

        $orderItem = $order->items()->create([
            'one_time_product_id' => $product->id,
            'quantity' => 1,
            'price_per_unit' => 10,
            'currency_id' => Currency::where('code', 'USD')->first()->id,
        ]);

        $transaction = Transaction::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'amount' => 100,
            'currency_id' => Currency::where('code', 'USD')->first()->id,
            'order_id' => $order->id,
            'status' => TransactionStatus::SUCCESS->value,
        ]);

        $transactionUuid = $transaction->uuid;

        $response = $this->get(route('invoice.generate', ['transactionUuid' => $transactionUuid]));
        $response->assertStatus(200);

        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));

    }

    public function test_invoice_generation_work_only_if_enabled()
    {
        config(['invoices.enabled' => false]);

        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);

        $transaction = Transaction::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'amount' => 100,
            'currency_id' => Currency::where('code', 'USD')->first()->id,
            'status' => TransactionStatus::SUCCESS->value,
        ]);

        $this->expectException(NotFoundHttpException::class);

        $this->get(route('invoice.generate', ['transactionUuid' => $transaction->uuid]));
    }
}
