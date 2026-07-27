<?php

namespace Themicly\Shopcrafty\Modules\Orders\Actions;

use Illuminate\Support\Facades\DB;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\Catalog\Models\Variant;
use Themicly\Shopcrafty\Modules\Orders\Exceptions\InsufficientStockException;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;

/**
 * Decrements inventory for an order's items, once. Prepaid orders commit at
 * placement; COD orders commit when confirmed (see OrderStatusService) so
 * unverified/likely-fake COD orders don't over-reserve stock. Idempotent via
 * the order's stock_committed flag; re-validates availability under a row lock
 * and throws if stock ran out between placement and commit.
 */
class CommitStock
{
    public function handle(Order $order): void
    {
        if ($order->stock_committed) {
            return;
        }

        DB::transaction(function () use ($order) {
            $order->loadMissing('items');

            foreach ($order->items as $item) {
                if ($item->variant_id) {
                    $variant = Variant::whereKey($item->variant_id)->lockForUpdate()->first();
                    if (! $variant || $variant->stock_qty < $item->qty) {
                        throw new InsufficientStockException("Not enough stock for {$item->name}.");
                    }
                } else {
                    $product = Product::whereKey($item->product_id)->lockForUpdate()->first();
                    if ($product && $product->track_inventory && $product->stock_qty < $item->qty) {
                        throw new InsufficientStockException("Not enough stock for {$item->name}.");
                    }
                }
            }

            foreach ($order->items as $item) {
                if ($item->variant_id) {
                    Variant::whereKey($item->variant_id)->decrement('stock_qty', $item->qty);
                } elseif (Product::whereKey($item->product_id)->where('track_inventory', true)->exists()) {
                    Product::whereKey($item->product_id)->decrement('stock_qty', $item->qty);
                }
            }

            $order->update(['stock_committed' => true]);
        });
    }
}
