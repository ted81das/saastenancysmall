<?php

namespace App\Http\Controllers;

use App\Dto\CartItemDto;
use App\Services\CalculationManager;
use App\Services\DiscountManager;
use App\Services\OneTimeProductManager;
use App\Services\PaymentProviders\PaymentManager;
use App\Services\SessionManager;

class ProductCheckoutController extends Controller
{
    public function __construct(
        private PaymentManager $paymentManager,
        private DiscountManager $discountManager,
        private OneTimeProductManager $productManager,
        private CalculationManager $calculationManager,
        private SessionManager $sessionManager,
    ) {

    }

    public function productCheckout()
    {
        $cartDto = $this->sessionManager->getCartDto();

        if (empty($cartDto->items)) {
            return redirect()->route('home');
        }

        $product = $this->productManager->getOneTimeProductById($cartDto->items[0]->productId);

        $totals = $this->calculationManager->calculateCartTotals($cartDto, auth()->user());

        $this->sessionManager->saveCartDto($cartDto);

        $paymentProviders = $this->paymentManager->getActivePaymentProviders();

        return view('checkout.product', [
            'product' => $product,
            'paymentProviders' => $paymentProviders,
            'totals' => $totals,
            'cartDto' => $cartDto,
            'successUrl' => route('checkout.product.success'),
        ]);
    }

    public function addToCart(string $productSlug, int $quantity = 1)
    {
        $cartDto = $this->sessionManager->clearCartDto();  // use getCartDto() instead of clearCartDto() when allowing full cart checkout with multiple items

        $product = $this->productManager->getProductWithPriceBySlug($productSlug);

        if ($product === null) {
            abort(404);
        }

        if (! $product->is_active) {
            abort(404);
        }

        if ($quantity < 1) {
            $quantity = 1;
        }

        if ($quantity > $product->max_quantity) {
            $quantity = $product->max_quantity;
        }

        // if product is already in cart, increase quantity
        foreach ($cartDto->items as $item) {
            if ($item->productId == $product->id) {
                $item->quantity += $quantity;
                $item->quantity = min($item->quantity, $product->max_quantity);
                $this->sessionManager->saveCartDto($cartDto);

                return redirect()->route('checkout.product');
            }
        }

        $cartItem = new CartItemDto();
        $cartItem->productId = $product->id;
        $cartItem->quantity = $quantity;

        $cartDto->items[] = $cartItem;

        $this->sessionManager->saveCartDto($cartDto);

        return redirect()->route('checkout.product');
    }

    public function productCheckoutSuccess()
    {
        $cartDto = $this->sessionManager->getCartDto();

        if ($cartDto->orderId === null) {
            return redirect()->route('home');
        }

        if ($cartDto->discountCode !== null) {
            $this->discountManager->redeemCodeForOrder($cartDto->discountCode, auth()->user(), $cartDto->orderId);
        }

        $this->sessionManager->clearCartDto();

        return view('checkout.product-thank-you');
    }
}
