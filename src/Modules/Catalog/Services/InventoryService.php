<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\Catalog\Models\StockAdjustment;
use Themicly\Shopcrafty\Modules\Catalog\Models\Variant;

class InventoryService
{
    /**
     * Adjust a product's stock by a delta (can be negative), logging the change.
     * Simple/digital products only — a variable product's stock_qty is just the
     * rolled-up sum of its variants (see syncParentStock() in VariantManager),
     * so adjusting it directly would desync it from the variants and get
     * silently overwritten the next time they're regenerated.
     */
    public function adjust(Product $product, int $delta, ?string $reason = null, ?string $actor = 'admin'): StockAdjustment
    {
        if ($product->type === 'variable') {
            throw new InvalidArgumentException("Adjust stock on {$product->name}'s individual variants instead — its total is a rolled-up sum.");
        }

        return DB::transaction(function () use ($product, $delta, $reason, $actor) {
            // Lock a fresh read so concurrent adjustments/order decrements don't
            // clobber each other with a stale baseline (CAT-09).
            $locked = Product::whereKey($product->id)->lockForUpdate()->first() ?? $product;
            $before = (int) $locked->stock_qty;
            $after = max(0, $before + $delta);

            $locked->update(['stock_qty' => $after]);
            $product->setAttribute('stock_qty', $after);

            return StockAdjustment::create([
                'product_id' => $product->id,
                'delta' => $after - $before,
                'before_qty' => $before,
                'after_qty' => $after,
                'reason' => $reason,
                'actor' => $actor,
            ]);
        });
    }

    /**
     * Adjust one variant's stock by a delta, then re-roll the parent product's
     * total — same rollup rule VariantManager::syncParentStock() maintains
     * after a matrix save, kept in sync here too so the inventory list's
     * product-level total is never stale after a variant-level adjustment.
     */
    public function adjustVariant(Variant $variant, int $delta, ?string $reason = null, ?string $actor = 'admin'): StockAdjustment
    {
        return DB::transaction(function () use ($variant, $delta, $reason, $actor) {
            $locked = Variant::whereKey($variant->id)->lockForUpdate()->first() ?? $variant;
            $before = (int) $locked->stock_qty;
            $after = max(0, $before + $delta);

            $locked->update(['stock_qty' => $after]);
            $variant->setAttribute('stock_qty', $after);

            Product::whereKey($variant->product_id)->update([
                'stock_qty' => Variant::where('product_id', $variant->product_id)->sum('stock_qty'),
            ]);

            return StockAdjustment::create([
                'product_id' => $variant->product_id,
                'variant_id' => $variant->id,
                'delta' => $after - $before,
                'before_qty' => $before,
                'after_qty' => $after,
                'reason' => $reason,
                'actor' => $actor,
            ]);
        });
    }

    /**
     * Bulk price change for the given products.
     *
     * @param  array<int, int>  $productIds
     * @param  'set'|'percent'  $mode  set to an absolute minor price, or adjust by a percentage
     */
    public function bulkPrice(array $productIds, string $mode, float $value): int
    {
        $products = Product::whereIn('id', $productIds)->get();

        foreach ($products as $product) {
            $price = $mode === 'percent'
                ? (int) round($product->price * (1 + $value / 100))
                : (int) round($value);

            $product->update(['price' => max(0, $price)]);
        }

        return $products->count();
    }
}
