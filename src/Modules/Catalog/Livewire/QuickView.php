<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;

/**
 * A single quick-view modal mounted once in the storefront layout. Product cards
 * open it by dispatching a 'quick-view' event with the product id — no page load.
 */
class QuickView extends Component
{
    public ?int $productId = null;

    public bool $open = false;

    #[On('quick-view')]
    public function show(int $productId): void
    {
        $this->productId = $productId;
        $this->open = true;
    }

    public function render()
    {
        $product = $this->productId
            ? Product::active()->with(['media', 'variants'])->find($this->productId)
            : null;

        return View::make('theme::livewire.quick-view', ['product' => $product]);
    }
}
