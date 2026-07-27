<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Support;

use Illuminate\Database\Eloquent\Builder;
use Themicly\Shopcrafty\Core\Module\AddonRegistry;
use Themicly\Shopcrafty\Modules\Catalog\Models\Brand;
use Themicly\Shopcrafty\Modules\Catalog\Models\Category;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;
use Themicly\Shopcrafty\Modules\Orders\Models\OrderItem;

/**
 * Shared storefront product filtering + sorting, used by both the classic
 * controller-rendered pages and the live Livewire browser so the two never drift.
 */
trait ProductFiltering
{
    /**
     * Normalize a multi-select facet selection (query string / checkbox input) to
     * positive integer ids. An empty result means "no constraint" — callers must
     * NOT apply a whereIn for it, so deselecting every option shows all products.
     *
     * @return array<int, int>
     */
    protected function normalizeIdSelection(mixed $ids): array
    {
        $ids = array_filter((array) $ids, fn ($id) => is_numeric($id) && (int) $id > 0);

        return array_values(array_unique(array_map('intval', $ids)));
    }

    /** Apply brand / category / price / in-stock filters from a normalized array. */
    protected function applyProductFilters(Builder $query, array $filters, bool $skipCategories = false): void
    {
        if (($brands = $this->normalizeIdSelection($filters['brands'] ?? [])) !== []) {
            $query->whereIn('brand_id', $brands);
        }

        if (! $skipCategories && ($cats = $this->normalizeIdSelection($filters['categories'] ?? [])) !== []) {
            $query->whereIn('category_id', $cats);
        }

        $decimals = (int) settings('localization.currency_decimals', 2);
        $factor = 10 ** $decimals;

        if (isset($filters['min']) && is_numeric($filters['min'])) {
            $query->where('price', '>=', (int) round(((float) $filters['min']) * $factor));
        }

        if (isset($filters['max']) && is_numeric($filters['max'])) {
            $query->where('price', '<=', (int) round(((float) $filters['max']) * $factor));
        }

        if (! empty($filters['in_stock'])) {
            $query->where(fn (Builder $q) => $q->where('track_inventory', false)->orWhere('stock_qty', '>', 0));
        }
    }

    /** Apply a sort key (latest / price / rating / best-selling). */
    protected function applyProductSort(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'top_rated' => app(AddonRegistry::class)->installed('reviews')
                ? $query->orderByDesc('reviews_avg')->orderByDesc('reviews_count')
                : $query->latest(),
            'best_selling' => $query->orderByDesc(
                OrderItem::query()
                    ->selectRaw('coalesce(sum(order_items.qty), 0)')
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->whereColumn('order_items.product_id', 'products.id')
                    ->whereIn('orders.status', Order::REVENUE_STATUSES)
            ),
            default => $query->latest(),
        };
    }

    /**
     * Brands that have at least one active (storefront-visible) product, optionally
     * limited to a category subtree — pass the same id set the product query uses
     * so the sidebar never offers a brand whose products aren't actually listed.
     *
     * @param  array<int, int>  $categoryIds
     */
    protected function brandOptions(array $categoryIds = [])
    {
        return Brand::where('is_active', true)
            ->whereIn('id', Product::active()
                ->when($categoryIds !== [], fn (Builder $q) => $q->whereIn('category_id', $categoryIds))
                ->whereNotNull('brand_id')
                ->select('brand_id'))
            ->orderBy('name')
            ->get();
    }

    /** Categories that have at least one active (storefront-visible) product. */
    protected function categoryOptions()
    {
        return Category::where('is_active', true)
            ->whereIn('id', Product::active()->whereNotNull('category_id')->select('category_id'))
            ->orderBy('name')
            ->get();
    }

    /** @return array<int, int> the category id plus all of its descendants */
    protected function categoryAndDescendantIds(Category $category): array
    {
        $ids = [$category->id];
        $frontier = [$category->id];

        while ($frontier) {
            $children = Category::whereIn('parent_id', $frontier)->pluck('id')->all();
            $frontier = array_values(array_diff($children, $ids));
            $ids = array_merge($ids, $frontier);
        }

        return $ids;
    }

    /** Escape LIKE metacharacters so a query of "%" doesn't match everything (CAT-13). */
    protected function escapeLike(string $value): string
    {
        return str_replace(['%', '_'], ['', ''], $value);
    }
}
