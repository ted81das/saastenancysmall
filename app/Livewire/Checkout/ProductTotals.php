<?php

namespace App\Livewire\Checkout;

use App\Constants\SessionConstants;
use App\Dto\CartDto;
use App\Dto\TotalsDto;
use App\Models\OneTimeProduct;
use App\Services\CalculationManager;
use App\Services\DiscountManager;
use Livewire\Attributes\On;
use Livewire\Component;

class ProductTotals extends Component
{
    public $page;

    public $subtotal;

    public $product;

    public $discountAmount;

    public $amountDue;

    public $currencyCode;

    public $code;

    private DiscountManager $discountManager;

    private CalculationManager $calculationManager;

    public function boot(DiscountManager $discountManager, CalculationManager $calculationManager)
    {
        $this->discountManager = $discountManager;
        $this->calculationManager = $calculationManager;
    }

    public function mount(TotalsDto $totals, OneTimeProduct $product, $page)
    {
        $this->page = $page;
        $this->product = $product;
        $this->subtotal = $totals->subtotal;
        $this->discountAmount = $totals->discountAmount;
        $this->amountDue = $totals->amountDue;
        $this->currencyCode = $totals->currencyCode;
    }

    private function getCartDto(): ?CartDto
    {
        return session()->get(SessionConstants::CART_DTO);
    }

    private function saveCartDto(CartDto $cartDto): void
    {
        session()->put(SessionConstants::CART_DTO, $cartDto);
    }

    public function add()
    {
        $code = $this->code;

        if ($code === null) {
            session()->flash('error', __('Please enter a discount code.'));

            return;
        }

        $isRedeemable = $this->discountManager->isCodeRedeemableForOneTimeProduct($code, auth()->user(), $this->product);

        if (! $isRedeemable) {
            session()->flash('error', __('This discount code is invalid.'));

            return;
        }

        $cartDto = $this->getCartDto();
        $cartDto->discountCode = $code;

        $this->saveCartDto($cartDto);

        $this->updateTotals();

        session()->flash('success', __('The discount code has been applied.'));
    }

    public function remove()
    {
        $cartDto = $this->getCartDto();
        $cartDto->discountCode = null;
        $this->saveCartDto($cartDto);

        session()->flash('success', __('The discount code has been removed.'));

        $this->updateTotals();
    }

    #[On('calculations-updated')]
    public function updateTotals()
    {
        $totals = $this->calculationManager->calculateCartTotals(
            $this->getCartDto(),
            auth()->user()
        );

        $this->subtotal = $totals->subtotal;
        $this->discountAmount = $totals->discountAmount;
        $this->amountDue = $totals->amountDue;
        $this->currencyCode = $totals->currencyCode;
    }

    public function render()
    {
        return view('livewire.checkout.product-totals', [
            'addedCode' => $this->getCartDto()->discountCode,
        ]);
    }
}
