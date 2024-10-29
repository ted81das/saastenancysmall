<?php

namespace App\Services;

use App\Constants\OrderStatus;
use App\Dto\CartDto;
use App\Events\Order\Ordered;
use App\Events\Order\OrderedRefunded;
use App\Exceptions\TenantException;
use App\Models\Currency;
use App\Models\OneTimeProduct;
use App\Models\Order;
use App\Models\PaymentProvider;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderManager
{
    public function __construct(
        private CalculationManager $calculationManager,
    ) {
    }

    public function create(
        User $user,
        Tenant $tenant,
        ?PaymentProvider $paymentProvider = null,
        ?int $totalAmount = null,
        ?int $discountTotal = null,
        ?int $totalAmountAfterDiscount = null,
        ?Currency $currency = null,
        ?array $orderItems = [],
        $paymentProviderOrderId = null,
    ): Order {
        $orderAttributes = [
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'status' => OrderStatus::NEW->value,
            'total_amount' => $totalAmount ?? 0,
            'tenant_id' => $tenant->id,
        ];

        if ($paymentProvider) {
            $orderAttributes['payment_provider_id'] = $paymentProvider->id;
        }

        if ($discountTotal) {
            $orderAttributes['total_discount_amount'] = $discountTotal;
        }

        if ($totalAmountAfterDiscount) {
            $orderAttributes['total_amount_after_discount'] = $totalAmountAfterDiscount;
        }

        if ($currency) {
            $orderAttributes['currency_id'] = $currency->id;
        }

        if ($paymentProviderOrderId) {
            $orderAttributes['payment_provider_order_id'] = $paymentProviderOrderId;
        }

        $order = Order::create($orderAttributes);

        if ($orderItems) {
            $order->items()->createMany($orderItems);
        }

        return $order;
    }

    public function findByUuidOrFail(string $uuid): Order
    {
        return Order::where('uuid', $uuid)->firstOrFail();
    }

    public function findByPaymentProviderOrderId(PaymentProvider $paymentProvider, string $paymentProviderOrderId): ?Order
    {
        return Order::where('payment_provider_id', $paymentProvider->id)
            ->where('payment_provider_order_id', $paymentProviderOrderId)
            ->first();
    }

    public function updateOrder(
        Order $order,
        array $data
    ): Order {
        $oldStatus = $order->status;
        $newStatus = $data['status'] ?? $oldStatus;
        $order->update($data);

        $this->handleDispatchingEvents(
            $oldStatus,
            $newStatus,
            $order
        );

        return $order;
    }

    private function handleDispatchingEvents(
        ?string $oldStatus,
        string|OrderStatus $newStatus,
        Order $order
    ): void {
        $newStatus = $newStatus instanceof OrderStatus ? $newStatus->value : $newStatus;

        if ($oldStatus !== $newStatus) {
            switch ($newStatus) {
                case OrderStatus::SUCCESS->value:
                    Ordered::dispatch($order);
                    break;
                case OrderStatus::REFUNDED->value:
                    OrderedRefunded::dispatch($order);
                    break;
            }
        }
    }

    public function refreshOrder(CartDto $cartDto, Order $order)
    {
        $existingProductIds = $order->items->pluck('one_time_product_id')->toArray();
        $newProductIds = [];
        foreach ($cartDto->items as $item) {
            $newProductIds[] = $item->productId;
        }

        $cartProductToQuantity = [];
        foreach ($cartDto->items as $item) {
            $cartProductToQuantity[$item->productId] = $item->quantity;
        }

        $productIdsToAdd = array_diff($newProductIds, $existingProductIds);
        $productIdsToRemove = array_diff($existingProductIds, $newProductIds);
        $productsToUpdate = array_intersect($existingProductIds, $newProductIds);
        $productsToAdd = OneTimeProduct::whereIn('id', $productIdsToAdd)->get();

        DB::transaction(function () use ($order, $productIdsToRemove, $productsToAdd, $cartDto, $cartProductToQuantity, $productsToUpdate) {

            foreach ($productIdsToRemove as $productId) {
                $order->items()->where('one_time_product_id', $productId)->delete();
            }

            foreach ($productsToAdd as $product) {
                $order->items()->create([
                    'one_time_product_id' => $product->id,
                    'quantity' => $cartProductToQuantity[$product->id],
                    'price_per_unit' => 0,
                ]);
            }

            foreach ($productsToUpdate as $productId) {
                $orderItem = $order->items()->where('one_time_product_id', $productId)->first();
                $orderItem->quantity = $cartProductToQuantity[$productId];
                $orderItem->save();
            }

            $order->save();

            $order->refresh();

            $this->calculationManager->calculateOrderTotals($order, auth()->user(), $cartDto->discountCode);

            $order->save();
        });
    }

    public function findNewByIdForUser(int $orderId, User $user): ?Order
    {
        return Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->where('status', OrderStatus::NEW)
            ->first();
    }

    public function hasOrderedProduct(User $user, string $productSlug): bool
    {
        $product = OneTimeProduct::where('slug', $productSlug)->first();

        if (! $product) {
            return false;
        }

        return $user->orders()->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.one_time_product_id', $product->id)
            ->where('orders.status', OrderStatus::SUCCESS)
            ->exists();
    }

    public function hasUserOrdered(?User $user, ?string $productSlug, ?Tenant $tenant = null): bool
    {
        if (! $user) {
            return false;
        }

        $tenant = $tenant ?? Filament::getTenant();

        if (! $tenant) {
            throw new TenantException('Could not resolve tenant: You either need to specify a tenant or be in a tenant context to check if a user purchased a product.');
        }

        $userTenant = $user->tenants()->where('tenant_id', $tenant->id)->first();

        if (! $userTenant) {
            return false;
        }

        if (! $productSlug) {
            return $userTenant->orders()
                ->where('status', OrderStatus::SUCCESS)
                ->exists();
        }

        return $userTenant->orders()
            ->where('status', OrderStatus::SUCCESS)
            ->whereHas('items', function ($query) use ($productSlug) {
                $query->whereHas('oneTimeProduct', function ($query) use ($productSlug) {
                    $query->where('slug', $productSlug);
                });
            })
            ->exists();
    }
}
