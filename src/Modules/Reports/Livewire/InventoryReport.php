<?php

namespace Themicly\Shopcrafty\Modules\Reports\Livewire;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;

class InventoryReport extends Component
{
    /** "No sales in the last N days" window for dead stock. */
    #[Url]
    public int $deadStockDays = 30;

    /** Financial/operational reports are owner-only, like the rest of Reports (RPT-08). */
    public function mount(): void
    {
        abort_unless(auth()->user()?->can('manage-config'), 403);
    }

    protected function trackedProducts()
    {
        return Product::query()->where('status', 'active')->where('track_inventory', true);
    }

    /** In stock but at/below the product's own low-stock threshold. */
    protected function lowStock()
    {
        return $this->trackedProducts()
            ->where('stock_qty', '>', 0)
            ->whereColumn('stock_qty', '<=', 'low_stock_threshold')
            ->with('media')
            ->orderBy('stock_qty')
            ->limit(12)
            ->get();
    }

    /** Nothing left to sell — the sales-blocking case, distinct from merely "low". */
    protected function outOfStock()
    {
        return $this->trackedProducts()
            ->where('stock_qty', '<=', 0)
            ->with('media')
            ->orderByDesc('updated_at')
            ->limit(12)
            ->get();
    }

    /**
     * Stock sitting on the shelf with zero units sold in the window — capital
     * tied up in inventory nobody's buying. Needs stock > 0 (a sold-out product
     * isn't "dead", it's simply out of stock — see outOfStock() above).
     */
    protected function deadStock()
    {
        $since = now()->subDays($this->deadStockDays);

        $soldRecently = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.placed_at', '>=', $since)
            ->distinct()
            ->pluck('order_items.product_id');

        return $this->trackedProducts()
            ->where('stock_qty', '>', 0)
            ->whereNotIn('id', $soldRecently)
            ->with('media')
            ->orderByDesc(DB::raw('stock_qty * COALESCE(cost_price, 0)'))
            ->limit(12)
            ->get();
    }

    /**
     * Highest units-sold-per-day over a fixed 30-day window — independent of the
     * dead-stock toggle above, since "what's moving" is a stable comparison
     * rather than a lookback the owner would want to shrink/grow.
     */
    protected function fastMoving()
    {
        $since = now()->subDays(30);

        $unitsByProduct = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.placed_at', '>=', $since)
            ->selectRaw('order_items.product_id, SUM(order_items.qty) as units')
            ->groupBy('order_items.product_id')
            ->orderByDesc('units')
            ->limit(8)
            ->pluck('units', 'product_id');

        $products = Product::with('media')->whereIn('id', $unitsByProduct->keys())->get()->keyBy('id');

        return $unitsByProduct->map(fn ($units, $id) => [
            'product' => $products->get($id),
            'units' => (int) $units,
            'perDay' => round($units / 30, 1),
        ])->filter(fn ($row) => $row['product'] !== null)->values();
    }

    /** Cost value, selling value, and the spread between them across all tracked stock. */
    protected function inventoryValue(): array
    {
        $row = $this->trackedProducts()
            ->where('stock_qty', '>', 0)
            ->selectRaw('
                COALESCE(SUM(stock_qty * COALESCE(cost_price, 0)), 0) as cost_value,
                COALESCE(SUM(stock_qty * price), 0) as selling_value
            ')
            ->first();

        $costValue = (int) ($row->cost_value ?? 0);
        $sellingValue = (int) ($row->selling_value ?? 0);

        return [
            'costValue' => $costValue,
            'sellingValue' => $sellingValue,
            'potentialProfit' => $sellingValue - $costValue,
        ];
    }

    public function render()
    {
        return View::make('reports::livewire.inventory-report', [
            'lowStock' => $this->lowStock(),
            'outOfStock' => $this->outOfStock(),
            'deadStock' => $this->deadStock(),
            'fastMoving' => $this->fastMoving(),
            'inventoryValue' => $this->inventoryValue(),
        ]);
    }
}
