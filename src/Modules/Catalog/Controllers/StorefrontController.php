<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Themicly\Shopcrafty\Core\Support\DemoMode;
use Themicly\Shopcrafty\Modules\Catalog\Models\Attribute;
use Themicly\Shopcrafty\Modules\Catalog\Models\Category;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\Catalog\Services\RecentlyViewed;
use Themicly\Shopcrafty\Modules\Catalog\Services\SearchTermRecorder;
use Themicly\Shopcrafty\Modules\Catalog\Support\ProductFiltering;

class StorefrontController
{
    use ProductFiltering;

    public function shop()
    {
        // Listing, filtering, sorting and pagination are handled live by the
        // <livewire:catalog.product-browser> component (no page reloads).
        return View::make('theme::shop');
    }

    public function category(string $slug)
    {
        $category = Category::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return View::make('theme::category', ['category' => $category]);
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->query('q'));

        // Popularity analytics. Deduped per session; must never break the
        // results page.
        rescue(fn () => app(SearchTermRecorder::class)->record($q), report: false);

        return View::make('theme::search', ['q' => $q]);
    }

    /**
     * Predictive suggestions (JSON) for the header search. The header only
     * calls this after its own 180ms debounce settles, i.e. once the shopper
     * has paused typing — not on every keystroke — so it's also the record
     * point for live-search analytics, same term normalization/session-dedupe
     * as an explicit submit. A minimum of 3 characters (rather than 2) keeps
     * both the suggestions and the analytics from firing on noise like "sh".
     */
    public function suggest(Request $request)
    {
        $q = trim((string) $request->query('q'));

        if (mb_strlen($q) < 3) {
            return response()->json([]);
        }

        rescue(fn () => app(SearchTermRecorder::class)->record($q), report: false);

        $products = Product::active()->where('name', 'like', '%'.$this->escapeLike($q).'%')->with('media')->limit(6)->get();

        return response()->json($products->map(fn (Product $p) => [
            'name' => $p->name,
            'url' => url('/product/'.$p->slug),
            'price' => format_money($p->price),
            'image' => $p->media->first()?->path,
        ])->all());
    }

    public function show(string $slug)
    {
        $product = Product::active()->where('slug', $slug)->with(['media', 'variants'])->firstOrFail();

        $this->trackView($product->id);

        $recentlyViewed = app(RecentlyViewed::class)->products(excludeId: $product->id);
        app(RecentlyViewed::class)->record($product->id);

        return View::make('theme::product', [
            'product' => $product,
            'optionGroups' => $this->variantOptionGroups($product),
            'recentlyViewed' => $recentlyViewed,
        ]);
    }

    /**
     * Group a variable product's variant options into per-attribute selectors
     * (e.g. Color, Size) with distinct values, attaching a swatch colour where the
     * attribute is colour-typed. Enables real swatch/pill selectors on the PDP.
     *
     * @return array<int, array{name: string, is_color: bool, values: array<int, array{value: string, color: ?string}>}>
     */
    protected function variantOptionGroups(Product $product): array
    {
        $groups = [];
        foreach ($product->variants as $variant) {
            foreach ((array) $variant->options as $attr => $value) {
                $groups[$attr] ??= [];
                if (! in_array($value, $groups[$attr], true)) {
                    $groups[$attr][] = $value;
                }
            }
        }

        if (empty($groups)) {
            return [];
        }

        // Resolve swatch colours from the attribute-value table (value → color_code).
        $colors = [];
        $attributes = Attribute::with('values')
            ->whereIn('name', array_keys($groups))->get();
        foreach ($attributes as $attribute) {
            foreach ($attribute->values as $value) {
                if ($value->color_code) {
                    $colors[$attribute->name][$value->value] = $value->color_code;
                }
            }
        }

        $out = [];
        foreach ($groups as $name => $values) {
            $mapped = array_map(fn ($v) => ['value' => $v, 'color' => $colors[$name][$v] ?? null], $values);

            $out[] = [
                'name' => $name,
                'is_color' => collect($mapped)->contains(fn ($v) => $v['color'] !== null),
                'values' => $mapped,
            ];
        }

        return $out;
    }

    protected function trackView(int $productId): void
    {
        // This writes via DB::table(), bypassing the Eloquent-level demo-mode
        // guard (see Themicly\Shopcrafty\Core\Support\DemoMode) — and product pages are GETs,
        // which that guard intentionally never blocks. Check explicitly so
        // demo visitors don't inflate real product-view analytics.
        if (DemoMode::enabled()) {
            return;
        }

        $today = now()->toDateString();

        // Insert-or-ignore then atomically increment — no unique-violation race on
        // the first concurrent view of the day (CAT-12).
        DB::table('catalog_product_views')->insertOrIgnore([
            'product_id' => $productId,
            'date' => $today,
            'count' => 0,
        ]);

        DB::table('catalog_product_views')
            ->where('product_id', $productId)
            ->where('date', $today)
            ->increment('count');
    }
}
