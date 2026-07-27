<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Livewire\WithPagination;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\Catalog\Models\StockAdjustment;
use Themicly\Shopcrafty\Modules\Catalog\Models\Variant;
use Themicly\Shopcrafty\Modules\Catalog\Services\InventoryService;

class Inventory extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $lowOnly = false;

    /** @var array<int, string> productId => delta */
    public array $delta = [];

    /** @var array<int, string> productId => reason */
    public array $reason = [];

    /** @var array<int, string> variantId => delta */
    public array $variantDelta = [];

    /** @var array<int, string> variantId => reason */
    public array $variantReason = [];

    /** @var array<int, int> */
    public array $selected = [];

    public string $priceMode = 'percent';

    public string $priceValue = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingLowOnly(): void
    {
        $this->resetPage();
    }

    public function applyAdjust(int $id, InventoryService $inventory): void
    {
        $delta = (int) ($this->delta[$id] ?? 0);

        if ($delta === 0) {
            return;
        }

        $inventory->adjust(Product::findOrFail($id), $delta, $this->reason[$id] ?? null);

        unset($this->delta[$id], $this->reason[$id]);
        $this->dispatch('toast', message: 'Stock adjusted', type: 'success');
    }

    public function applyVariantAdjust(int $variantId, InventoryService $inventory): void
    {
        $delta = (int) ($this->variantDelta[$variantId] ?? 0);

        if ($delta === 0) {
            return;
        }

        $inventory->adjustVariant(Variant::findOrFail($variantId), $delta, $this->variantReason[$variantId] ?? null);

        unset($this->variantDelta[$variantId], $this->variantReason[$variantId]);
        $this->dispatch('toast', message: 'Variant stock adjusted', type: 'success');
    }

    /** One-click ±1 nudge — no reason needed, for the common "sold one" case. */
    public function quickAdjust(int $id, int $step, InventoryService $inventory): void
    {
        $inventory->adjust(Product::findOrFail($id), $step, 'Quick adjust');
        $this->dispatch('toast', message: 'Stock adjusted', type: 'success');
    }

    public function quickAdjustVariant(int $variantId, int $step, InventoryService $inventory): void
    {
        $inventory->adjustVariant(Variant::findOrFail($variantId), $step, 'Quick adjust');
        $this->dispatch('toast', message: 'Variant stock adjusted', type: 'success');
    }

    public function applyBulkPrice(InventoryService $inventory): void
    {
        $data = $this->validate([
            'selected' => ['required', 'array', 'min:1'],
            'priceMode' => ['in:set,percent'],
            // A "set" price can't be negative (it would silently clamp every
            // selected product to 0); a percentage adjustment floors at -100%.
            'priceValue' => ['required', 'numeric', $this->priceMode === 'set' ? 'min:0' : 'min:-100'],
        ], [], ['selected' => 'products']);

        $decimals = (int) settings('localization.currency_decimals', 2);
        $value = $data['priceMode'] === 'set'
            ? ((float) $data['priceValue']) * (10 ** $decimals) // major → minor
            : (float) $data['priceValue'];

        $count = $inventory->bulkPrice($this->selected, $data['priceMode'], $value);

        $this->reset('selected', 'priceValue');
        $this->dispatch('toast', message: "Updated pricing on {$count} product(s)", type: 'success');
    }

    public function render()
    {
        $products = Product::where('track_inventory', true)
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->lowOnly, fn ($q) => $q->whereColumn('stock_qty', '<=', 'low_stock_threshold'))
            // Variants ordered so the breakdown reads the same as the variant
            // manager's matrix (position, falling back to id for older rows).
            ->with(['variants' => fn ($q) => $q->orderBy('position')->orderBy('id')])
            ->orderBy('stock_qty')
            ->paginate(15);

        return View::make('catalog::livewire.inventory', [
            'products' => $products,
            'lowCount' => Product::where('track_inventory', true)->whereColumn('stock_qty', '<=', 'low_stock_threshold')->count(),
            'adjustments' => StockAdjustment::with(['product', 'variant'])->latest()->limit(8)->get(),
        ]);
    }
}
