<?php

namespace Themicly\Shopcrafty\Modules\Orders\Actions;

use Illuminate\Support\Facades\DB;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\Catalog\Models\Variant;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;

/**
 * Returns an order's committed inventory to stock — used when a committed order
 * is cancelled or returned. Idempotent via the stock_committed flag (an order
 * that never committed stock, e.g. an unverified COD order, restocks nothing).
 *
 * Pass $quantities (order_item_id => qty) to restock only part of the order
 * (a partial return/refund); the order stays "committed" for the rest. Omit it
 * to release the whole order's inventory (cancel / full return).
 *
 * @param  array<int, int>|null  $quantities
 */
class RestockOrder
{
    public function handle(Order $order, ?array $quantities = null): void
    {
        if (! $order->stock_committed) {
            return;
        }

        DB::transaction(function () use ($order, $quantities) {
            $order->loadMissing('items');

            foreach ($order->items as $item) {
                $qty = $quantities === null ? (int) $item->qty : (int) ($quantities[$item->id] ?? 0);

                if ($qty <= 0) {
                    continue;
                }

                if ($item->variant_id) {
                    Variant::whereKey($item->variant_id)->increment('stock_qty', $qty);
                } elseif (Product::whereKey($item->product_id)->where('track_inventory', true)->exists()) {
                    Product::whereKey($item->product_id)->increment('stock_qty', $qty);
                }
            }

            // Only a full restock releases the whole order's commitment; a partial
            // return leaves the remaining lines committed.
            if ($quantities === null) {
                $order->update(['stock_committed' => false]);
            }
        });
    }
}
