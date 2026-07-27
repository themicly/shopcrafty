<?php

namespace Themicly\Shopcrafty\Modules\Orders\Services;

use Illuminate\Support\Str;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;
use Themicly\Shopcrafty\Modules\Orders\Models\OrderItem;

/**
 * Turns a paid order's digital lines into download grants (and a per-line
 * license key). Idempotent — safe to re-run on every status change; existing
 * grants are left untouched.
 */
class DigitalFulfillmentService
{
    /**
     * Grant downloads for every digital line on the order.
     *
     * @return int number of digital lines fulfilled
     */
    public function fulfill(Order $order): int
    {
        $order->loadMissing('items.product.files');
        $fulfilled = 0;

        foreach ($order->items as $item) {
            $product = $item->product;

            if (! $product || ! $product->isDigital()) {
                continue;
            }

            $fulfilled++;

            if (! $item->license_key) {
                $item->update(['license_key' => $this->generateLicenseKey()]);
            }

            foreach ($product->files as $file) {
                $item->downloadGrants()->firstOrCreate(
                    ['product_file_id' => $file->id],
                    ['order_id' => $order->id, 'product_id' => $product->id],
                );
            }
        }

        return $fulfilled;
    }

    /** Whether an order has any digital line at all (no side effects). */
    public function hasDigitalItems(Order $order): bool
    {
        $order->loadMissing('items.product');

        return $order->items->contains(fn (OrderItem $item) => $item->product?->isDigital());
    }

    protected function generateLicenseKey(): string
    {
        return collect(range(1, 4))
            ->map(fn () => Str::upper(Str::random(4)))
            ->implode('-');
    }
}
