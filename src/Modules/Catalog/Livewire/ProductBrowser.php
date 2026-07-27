<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Livewire;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Themicly\Shopcrafty\Modules\Catalog\Models\Attribute;
use Themicly\Shopcrafty\Modules\Catalog\Models\Brand;
use Themicly\Shopcrafty\Modules\Catalog\Models\Category;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\Catalog\Models\Variant;
use Themicly\Shopcrafty\Modules\Catalog\Support\ProductFiltering;

/**
 * Live storefront product listing — powers shop, category and search with
 * instant (no-reload) filtering, sorting and pagination.
 */
class ProductBrowser extends Component
{
    use ProductFiltering;
    use WithPagination;

    /** shop | category | search */
    public string $context = 'shop';

    public ?int $categoryId = null;

    public string $q = '';

    /** @var array<int, int> */
    #[Url(as: 'categories')]
    public array $categoryFilter = [];

    /** @var array<int, int> */
    #[Url(as: 'brands')]
    public array $brandFilter = [];

    /**
     * Attribute facet selection, keyed by attribute name → chosen values,
     * e.g. ['Color' => ['Red', 'Blue'], 'Size' => ['L']].
     *
     * @var array<string, array<int, string>>
     */
    #[Url(as: 'options')]
    public array $optionFilter = [];

    #[Url]
    public ?string $min = null;

    #[Url]
    public ?string $max = null;

    #[Url(as: 'in_stock')]
    public bool $inStock = false;

    #[Url(as: 'rating')]
    public ?string $minRating = null;

    #[Url]
    public string $sort = 'latest';

    public function mount(string $context = 'shop', ?int $categoryId = null, string $q = ''): void
    {
        $this->context = $context;
        $this->categoryId = $categoryId;
        $this->q = $q;
    }

    /** Any filter/sort change returns to page 1. */
    public function updated(string $property): void
    {
        if ($property !== 'page') {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset('categoryFilter', 'brandFilter', 'optionFilter', 'min', 'max', 'inStock', 'minRating');
        $this->resetPage();
    }

    public function removeCategory(int $id): void
    {
        $this->categoryFilter = array_values(array_diff($this->categoryFilter, [$id]));
        $this->resetPage();
    }

    public function removeBrand(int $id): void
    {
        $this->brandFilter = array_values(array_diff($this->brandFilter, [$id]));
        $this->resetPage();
    }

    public function clearPrice(): void
    {
        $this->min = null;
        $this->max = null;
        $this->resetPage();
    }

    protected function reviewsEnabled(): bool
    {
        return (bool) settings('catalog.reviews_enabled', true);
    }

    /** Memoized per request — contextQuery() and the facet lists reuse it. */
    private ?array $contextCategoryIdsCache = null;

    /**
     * The category subtree (id + descendants) a category page is scoped to;
     * empty on shop/search pages. Sidebar option lists use the same set so they
     * never offer choices outside the products actually listed.
     *
     * @return array<int, int>
     */
    protected function contextCategoryIds(): array
    {
        if ($this->contextCategoryIdsCache !== null) {
            return $this->contextCategoryIdsCache;
        }

        $ids = [];
        if ($this->context === 'category' && $this->categoryId) {
            $category = Category::find($this->categoryId);
            $ids = $category ? $this->categoryAndDescendantIds($category) : [];
        }

        return $this->contextCategoryIdsCache = $ids;
    }

    /** True when any sidebar filter is applied (used by the zero-results state). */
    protected function hasActiveFilters(): bool
    {
        return $this->normalizeIdSelection($this->categoryFilter) !== []
            || $this->normalizeIdSelection($this->brandFilter) !== []
            || array_filter($this->optionFilter) !== []
            || is_numeric($this->min)
            || is_numeric($this->max)
            || $this->inStock
            || (is_numeric($this->minRating) && $this->reviewsEnabled());
    }

    /** Product query with only the context (shop / category / search) applied. */
    protected function contextQuery(): Builder
    {
        $query = Product::active();

        if (($contextCategoryIds = $this->contextCategoryIds()) !== []) {
            $query->whereIn('category_id', $contextCategoryIds);
        }

        if ($this->context === 'search' && trim($this->q) !== '') {
            $like = '%'.$this->escapeLike(trim($this->q)).'%';
            $query->where(fn (Builder $w) => $w
                ->where('name', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhere('sku', 'like', $like));
        }

        return $query;
    }

    /**
     * Context query plus all active filters, skipping any named dimensions so a
     * facet can count/enumerate options as if that dimension were not applied.
     *
     * @param  array<int, string>  $skip  any of: brand, category, option, rating
     */
    protected function scopedQuery(array $skip = []): Builder
    {
        $query = $this->contextQuery();

        $this->applyProductFilters($query, [
            'brands' => in_array('brand', $skip, true) ? [] : $this->brandFilter,
            'categories' => in_array('category', $skip, true) ? [] : $this->categoryFilter,
            'min' => $this->min,
            'max' => $this->max,
            'in_stock' => $this->inStock,
        ], skipCategories: $this->context === 'category');

        if (! in_array('option', $skip, true)) {
            $this->applyOptionFilter($query);
        }

        if (! in_array('rating', $skip, true)) {
            $this->applyRatingFilter($query);
        }

        return $query;
    }

    protected function baseQuery(): Builder
    {
        // withCount('variants') feeds the card's variant badge without an N+1.
        return $this->applyProductSort($this->scopedQuery()->with('media')->withCount('variants'), $this->sort);
    }

    /**
     * Toggle a single attribute-facet value (e.g. Color=Red) on or off. Explicit
     * array handling here sidesteps Livewire's nested-checkbox binding pitfall,
     * where checking a swatch bound to `optionFilter.Color` set it to boolean
     * `true` instead of `['Red']` and the filter matched nothing (CAT-17).
     */
    public function toggleOption(string $attr, string $value): void
    {
        $current = array_values(array_filter(
            (array) ($this->optionFilter[$attr] ?? []),
            fn ($v) => $v !== null && $v !== ''
        ));

        if (in_array($value, $current, true)) {
            $current = array_values(array_filter($current, fn ($v) => $v !== $value));
        } else {
            $current[] = $value;
        }

        if ($current === []) {
            unset($this->optionFilter[$attr]);
        } else {
            $this->optionFilter[$attr] = $current;
        }

        $this->resetPage();
    }

    /** Is a given attribute-facet value currently selected? */
    public function optionChecked(string $attr, string $value): bool
    {
        return in_array($value, (array) ($this->optionFilter[$attr] ?? []), true);
    }

    /** Restrict to products having at least one variant matching each selected attribute. */
    protected function applyOptionFilter(Builder $query): void
    {
        foreach ($this->optionFilter as $attr => $values) {
            $values = array_values(array_filter((array) $values, fn ($v) => $v !== null && $v !== ''));
            if (empty($values)) {
                continue;
            }

            $query->whereHas('variants', function (Builder $q) use ($attr, $values) {
                $q->where(function (Builder $w) use ($attr, $values) {
                    foreach ($values as $value) {
                        $w->orWhere('options->'.$attr, $value);
                    }
                });
            });
        }
    }

    /** Restrict to products meeting a minimum average rating (when reviews are enabled). */
    protected function applyRatingFilter(Builder $query): void
    {
        if ($this->reviewsEnabled() && is_numeric($this->minRating)) {
            $query->where('reviews_avg', '>=', (float) $this->minRating);
        }
    }

    /**
     * Match count per option for a facet dimension, respecting every other active
     * filter (i.e. computed with that dimension itself removed from scope).
     *
     * @return array<int, int> keyed by id → count
     */
    protected function facetCounts(string $column, string $skip): array
    {
        return $this->scopedQuery([$skip])
            ->whereNotNull($column)
            ->selectRaw("{$column} as fid, count(*) as c")
            ->groupBy($column)
            ->pluck('c', 'fid')
            ->map(fn ($c) => (int) $c)
            ->all();
    }

    /**
     * Attribute (colour / size …) facets built from the variants of the in-scope
     * products, mirroring StorefrontController::variantOptionGroups().
     *
     * @return array<int, array{name: string, is_color: bool, values: array<int, array{value: string, color: ?string}>}>
     */
    protected function optionFacets(): array
    {
        $productIds = $this->scopedQuery(['option'])->pluck('id');

        if ($productIds->isEmpty()) {
            return [];
        }

        $groups = [];
        foreach (Variant::whereIn('product_id', $productIds)->pluck('options') as $options) {
            foreach ((array) $options as $attr => $value) {
                if ($value === null || $value === '') {
                    continue;
                }
                $groups[$attr][$value] = true;
            }
        }

        if (empty($groups)) {
            return [];
        }

        // Resolve swatch colours from the attribute-value table (value → color_code).
        $colors = [];
        foreach (Attribute::with('values')->whereIn('name', array_keys($groups))->get() as $attribute) {
            foreach ($attribute->values as $value) {
                if ($value->color_code) {
                    $colors[$attribute->name][$value->value] = $value->color_code;
                }
            }
        }

        $out = [];
        foreach ($groups as $name => $values) {
            $mapped = array_map(
                fn ($v) => ['value' => (string) $v, 'color' => $colors[$name][$v] ?? null],
                array_keys($values)
            );

            $out[] = [
                'name' => $name,
                'is_color' => collect($mapped)->contains(fn ($v) => $v['color'] !== null),
                'values' => $mapped,
            ];
        }

        return $out;
    }

    /** Human-readable chips for the currently-applied filters. */
    protected function activeChips(): array
    {
        $chips = [];

        foreach (Category::whereIn('id', $this->categoryFilter)->pluck('name', 'id') as $id => $name) {
            $chips[] = ['type' => 'category', 'id' => $id, 'label' => $name];
        }
        foreach (Brand::whereIn('id', $this->brandFilter)->pluck('name', 'id') as $id => $name) {
            $chips[] = ['type' => 'brand', 'id' => $id, 'label' => $name];
        }
        if (is_numeric($this->min) || is_numeric($this->max)) {
            $chips[] = ['type' => 'price', 'id' => 0, 'label' => 'Price: '.($this->min ?: '0').'–'.($this->max ?: '∞')];
        }

        return $chips;
    }

    /**
     * When nothing matches, offer a relaxed (any-term) match on the search query
     * plus a few popular products so the shopper never hits a dead end.
     *
     * @return array{relaxed: Collection<int, Product>, popular: Collection<int, Product>}
     */
    protected function suggestions(): array
    {
        $relaxed = collect();

        if ($this->context === 'search' && trim($this->q) !== '') {
            $terms = array_filter(preg_split('/\s+/', trim($this->q)) ?: [], fn ($t) => trim($t) !== '');

            if ($terms) {
                $relaxed = Product::active()->with('media')
                    ->where(function (Builder $w) use ($terms) {
                        foreach ($terms as $term) {
                            $escaped = $this->escapeLike($term);
                            if ($escaped !== '') {
                                $w->orWhere('name', 'like', '%'.$escaped.'%');
                            }
                        }
                    })
                    ->limit(4)->get();
            }
        }

        $popular = Product::active()->with('media')
            ->when($relaxed->isNotEmpty(), fn (Builder $q) => $q->whereNotIn('id', $relaxed->pluck('id')))
            ->when($this->reviewsEnabled(), fn (Builder $q) => $q->orderByDesc('reviews_count'))
            ->latest()
            ->limit(4)->get();

        return ['relaxed' => $relaxed, 'popular' => $popular];
    }

    public function render()
    {
        $products = $this->baseQuery()->paginate(12);

        // Price bounds (major units) for the range slider — from the whole in-context
        // set (before filters) so the track is stable and never collapses.
        $bounds = $this->contextQuery()->getQuery()
            ->selectRaw('min(price) as lo, max(price) as hi')->first();
        $factor = 10 ** (int) settings('localization.currency_decimals', 2);
        $priceFloor = (int) floor((int) ($bounds->lo ?? 0) / $factor);
        $priceCeil = (int) ceil((int) ($bounds->hi ?? 0) / $factor);
        if ($priceCeil <= $priceFloor) {
            $priceCeil = $priceFloor + 1;
        }

        return View::make('theme::livewire.product-browser', [
            'products' => $products,
            'priceFloor' => $priceFloor,
            'priceCeil' => $priceCeil,
            'brands' => $this->brandOptions($this->contextCategoryIds()),
            'categories' => $this->context === 'category' ? collect() : $this->categoryOptions(),
            'categoryCounts' => $this->context === 'category' ? [] : $this->facetCounts('category_id', 'category'),
            'brandCounts' => $this->facetCounts('brand_id', 'brand'),
            'optionFacets' => $this->optionFacets(),
            'reviewsEnabled' => $this->reviewsEnabled(),
            'activeChips' => $this->activeChips(),
            'hasFilters' => $this->hasActiveFilters(),
            'suggestions' => $products->isEmpty() ? $this->suggestions() : null,
        ]);
    }
}
