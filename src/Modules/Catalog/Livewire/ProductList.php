<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Livewire;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Themicly\Shopcrafty\Modules\Catalog\Events\ProductCreated;
use Themicly\Shopcrafty\Modules\Catalog\Events\ProductUpdated;
use Themicly\Shopcrafty\Modules\Catalog\Models\Category;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;

class ProductList extends Component
{
    use WithPagination;

    /** Re-render (picks up newly imported rows) when the import drawer finishes. */
    #[On('products-imported')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public string $categoryFilter = '';

    #[Url]
    public string $view = 'table';

    #[Url]
    public int $perPage = 25;

    /** @var array<int, int> */
    public array $selected = [];

    // --- Bulk field edit (TASK #15) ---
    /** '' = leave unchanged; '0' = uncategorized; otherwise a category id. */
    public string $bulkCategory = '';

    /** '' = leave unchanged; otherwise draft|active|archived. */
    public string $bulkStatus = '';

    /** '' = leave unchanged; set|increase|decrease. */
    public string $bulkPriceMode = '';

    /** Major-unit amount (set) or percentage (increase/decrease). */
    public string $bulkPriceValue = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    protected function baseQuery()
    {
        return Product::query()
            ->with(['category.parent', 'media'])
            ->when($this->search !== '', fn ($q) => $q->where(function ($w) {
                $w->where('name', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%");
            }))
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->categoryFilter !== '', fn ($q) => $q->where('category_id', $this->categoryFilter))
            ->latest();
    }

    public function bulk(string $action): void
    {
        if (empty($this->selected)) {
            return;
        }

        $query = Product::whereIn('id', $this->selected);

        match ($action) {
            'activate' => $query->update(['status' => 'active', 'published_at' => now()]),
            'archive' => $query->update(['status' => 'archived']),
            'delete' => $query->delete(),
            default => null,
        };

        $count = count($this->selected);
        $this->selected = [];
        $this->dispatch('toast', message: "{$count} product(s) updated", type: 'success');
    }

    public function delete(int $id): void
    {
        Product::whereKey($id)->delete();
        $this->selected = array_values(array_diff($this->selected, [$id]));
        $this->dispatch('toast', message: 'Product deleted', type: 'success');
    }

    /**
     * Duplicate a product into a new draft: copies core fields, variants and
     * media rows. Name gets a "(Copy)" suffix; slug is regenerated uniquely and
     * SKUs are blanked so the unique SKU constraints (product + variant) never
     * collide with the source. Redirects to the new product's edit screen.
     */
    public function clone(int $id): void
    {
        $original = Product::with(['variants', 'media'])->findOrFail($id);

        $copy = DB::transaction(function () use ($original) {
            $copy = $original->replicate([
                'slug', 'sku', 'published_at', 'created_at', 'updated_at',
            ]);
            $copy->name = $original->name.' (Copy)';
            $copy->slug = null; // HasSlug generates a unique slug on save
            $copy->sku = null;  // avoid the unique SKU constraint
            $copy->status = 'draft';
            $copy->published_at = null;
            $copy->save();

            foreach ($original->variants as $variant) {
                $copy->variants()->create([
                    'options' => $variant->options,
                    'options_key' => $variant->options_key,
                    'sku' => null, // unique constraint on catalog_variants.sku
                    'price' => $variant->price,
                    'compare_at_price' => $variant->compare_at_price,
                    'stock_qty' => $variant->stock_qty,
                    'image_id' => null, // old media ids don't map to the copy
                    'position' => $variant->position,
                ]);
            }

            foreach ($original->media as $media) {
                $copy->media()->create([
                    'media_id' => $media->media_id,
                    'path' => $media->path,
                    'position' => $media->position,
                    'is_featured' => $media->is_featured,
                ]);
            }

            return $copy;
        });

        event(new ProductCreated($copy));
        $this->dispatch('toast', message: 'Product duplicated', type: 'success');
        $this->redirectRoute('admin.catalog.products.edit', $copy, navigate: true);
    }

    /**
     * Apply category / price / status changes to all selected products in one
     * transaction, firing ProductUpdated per product so downstream stays in sync.
     */
    public function bulkEdit(): void
    {
        if (empty($this->selected)) {
            return;
        }

        if ($this->bulkPriceMode !== '') {
            $this->validate(['bulkPriceValue' => ['required', 'numeric', 'min:0']]);
        }

        $products = Product::whereIn('id', $this->selected)->get();
        $decimals = (int) settings('localization.currency_decimals', 2);

        DB::transaction(function () use ($products, $decimals) {
            foreach ($products as $product) {
                $changes = [];

                if ($this->bulkCategory !== '') {
                    $changes['category_id'] = $this->bulkCategory === '0' ? null : (int) $this->bulkCategory;
                }

                if ($this->bulkStatus !== '') {
                    $changes['status'] = $this->bulkStatus;
                    if ($this->bulkStatus === 'active') {
                        $changes['published_at'] = $product->published_at ?? now();
                    }
                }

                if ($this->bulkPriceMode !== '' && $this->bulkPriceValue !== '') {
                    $value = (float) $this->bulkPriceValue;
                    $changes['price'] = match ($this->bulkPriceMode) {
                        'set' => (int) round($value * (10 ** $decimals)),
                        'increase' => (int) round($product->price * (1 + $value / 100)),
                        'decrease' => max(0, (int) round($product->price * (1 - $value / 100))),
                        default => $product->price,
                    };
                }

                if ($changes !== []) {
                    $product->update($changes);
                    event(new ProductUpdated($product));
                }
            }
        });

        $count = $products->count();
        $this->reset('bulkCategory', 'bulkStatus', 'bulkPriceMode', 'bulkPriceValue');
        $this->selected = [];
        $this->dispatch('toast', message: "{$count} products updated", type: 'success');
        $this->dispatch('close-modal', 'bulk-edit');
    }

    /** Store-wide inventory summary shown as stat cards (independent of filters). */
    protected function stats(): array
    {
        $lowStock = Product::where('track_inventory', true)
            ->whereColumn('stock_qty', '<=', 'low_stock_threshold')
            ->count();

        return [
            'total' => Product::count(),
            'stock' => (int) Product::sum('stock_qty'),
            'low' => $lowStock,
            'value' => (int) Product::selectRaw('COALESCE(SUM(price * stock_qty), 0) as v')->value('v'),
        ];
    }

    public function render()
    {
        return View::make('catalog::livewire.product-list', [
            'products' => $this->baseQuery()->paginate($this->perPage),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'stats' => $this->stats(),
        ]);
    }
}
