<?php

namespace Themicly\Shopcrafty\Modules\Marketing\Services;

use Illuminate\Support\Collection;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\Marketing\Models\ProductPair;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;

class BoughtTogether
{
    /**
     * Products to recommend alongside the given product. An owner-curated list
     * (relatedProducts) wins; otherwise fall back to automatic co-occurrence.
     */
    public function forProduct(int $productId, int $limit = 4): Collection
    {
        $curated = Product::whereKey($productId)->first()
            ?->relatedProducts()
            ->where('status', 'active')
            ->with('media')
            ->limit($limit)
            ->get();

        if ($curated && $curated->isNotEmpty()) {
            return $curated;
        }

        $ids = ProductPair::where('product_id', $productId)
            ->orderByDesc('weight')
            ->limit($limit)
            ->pluck('paired_product_id');

        if ($ids->isEmpty()) {
            return collect();
        }

        // Preserve the weight order the ids came back in.
        return Product::active()->whereIn('id', $ids)->with('media')->get()
            ->sortBy(fn ($p) => $ids->search($p->id))->values();
    }

    /** Increment co-occurrence weights for the items in a single order. */
    public function recordOrder(Order $order): void
    {
        $ids = $order->items->pluck('product_id')->filter()->unique()->values()->all();

        foreach ($ids as $a) {
            foreach ($ids as $b) {
                if ($a === $b) {
                    continue;
                }

                $pair = ProductPair::firstOrNew(['product_id' => $a, 'paired_product_id' => $b]);
                $pair->weight = ($pair->weight ?? 0) + 1;
                $pair->save();
            }
        }
    }
}
