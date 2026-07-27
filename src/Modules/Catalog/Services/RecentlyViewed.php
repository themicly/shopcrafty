<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Services;

use Illuminate\Support\Collection;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;

/**
 * Tracks the products a shopper has recently viewed. Ids live in the session,
 * capped at {@see self::CAP}, most-recent first and de-duplicated, so the PDP can
 * render a "Recently viewed" strip without any per-account persistence.
 */
class RecentlyViewed
{
    protected const SESSION_KEY = 'recently_viewed';

    protected const CAP = 8;

    /** Record a product view, moving it to the front of the list. */
    public function record(int $productId): void
    {
        $ids = array_values(array_diff($this->ids(), [$productId]));
        array_unshift($ids, $productId);

        session()->put(self::SESSION_KEY, array_slice($ids, 0, self::CAP));
    }

    /** @return array<int, int> most-recent-first product ids */
    public function ids(): array
    {
        return array_values(array_map('intval', session()->get(self::SESSION_KEY, [])));
    }

    /**
     * Active recently-viewed products in view order, optionally excluding one id
     * (typically the product currently being viewed).
     *
     * @return Collection<int, Product>
     */
    public function products(?int $excludeId = null): Collection
    {
        $ids = array_values(array_diff($this->ids(), array_filter([$excludeId])));

        if (empty($ids)) {
            return collect();
        }

        $products = Product::active()->whereIn('id', $ids)->with('media')->get()->keyBy('id');

        // Preserve most-recent-first ordering (whereIn does not guarantee order).
        return collect($ids)->map(fn (int $id) => $products->get($id))->filter()->values();
    }
}
