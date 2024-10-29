<?php

namespace App\View\Components\Products;

use App\Services\OneTimeProductManager;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class One extends Component
{
    public function __construct(
        private OneTimeProductManager $productManager,
        public string $slug,
    ) {

    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|\Closure|string
    {
        return view('components.products.one', $this->calculateViewData());
    }

    protected function calculateViewData()
    {
        $product = $this->productManager->getProductWithPriceBySlug($this->slug);

        return [
            'product' => $product,
        ];
    }
}
