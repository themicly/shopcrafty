<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Livewire;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Themicly\Shopcrafty\Ai\AiNotConfiguredException;
use Themicly\Shopcrafty\Ai\AiRequestException;
use Themicly\Shopcrafty\Ai\AiService;
use Themicly\Shopcrafty\Ai\Generators\ProductContentGenerator;
use Themicly\Shopcrafty\Core\Module\AddonRegistry;
use Themicly\Shopcrafty\Modules\Catalog\Events\ProductArchived;
use Themicly\Shopcrafty\Modules\Catalog\Events\ProductCreated;
use Themicly\Shopcrafty\Modules\Catalog\Events\ProductPublished;
use Themicly\Shopcrafty\Modules\Catalog\Events\ProductUpdated;
use Themicly\Shopcrafty\Modules\Catalog\Models\Brand;
use Themicly\Shopcrafty\Modules\Catalog\Models\Category;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\Catalog\Models\ProductFile;
use Themicly\Shopcrafty\Modules\Catalog\Models\ProductMedia;
use Themicly\Shopcrafty\Modules\Catalog\Services\DigitalAssetService;
use Themicly\Shopcrafty\Modules\Settings\Services\MediaService;

class ProductBuilder extends Component
{
    use WithFileUploads;

    public ?int $productId = null;

    public string $status = 'draft';

    public ?string $savedAt = null;

    // General
    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public string $type = 'simple';

    public ?int $categoryId = null;

    public ?int $brandId = null;

    // Pricing (major-unit strings, converted to minor on save)
    public string $price = '';

    public string $compareAtPrice = '';

    public string $costPrice = '';

    // Inventory
    public string $sku = '';

    public string $barcode = '';

    public string $stockQty = '0';

    public bool $trackInventory = true;

    public string $lowStockThreshold = '0';

    // Shipping
    public bool $requiresShipping = true;

    public string $weight = '';

    // SEO
    public string $seoTitle = '';

    public string $seoDescription = '';

    // Media
    public string $mediaUrl = '';

    /** @var array<int, mixed> pending uploads */
    public array $photos = [];

    /** @var array<int, mixed> pending digital-asset uploads */
    public array $digitalUploads = [];

    // Curated "recommended / bought with" products.
    /** @var array<int, int> */
    public array $relatedIds = [];

    public string $relatedSearch = '';

    public function mount(?int $product = null): void
    {
        if ($product) {
            $this->loadProduct(Product::findOrFail($product));
        }
    }

    protected function loadProduct(Product $product): void
    {
        $this->productId = $product->id;
        $this->status = $product->status;
        $this->name = $product->name;
        $this->slug = $product->slug;
        $this->description = (string) $product->description;
        $this->type = $product->type;
        $this->categoryId = $product->category_id;
        $this->brandId = $product->brand_id;
        $this->price = $this->toMajor($product->price);
        $this->compareAtPrice = $product->compare_at_price !== null ? $this->toMajor($product->compare_at_price) : '';
        $this->costPrice = $product->cost_price !== null ? $this->toMajor($product->cost_price) : '';
        $this->sku = (string) $product->sku;
        $this->barcode = (string) $product->barcode;
        $this->stockQty = (string) $product->stock_qty;
        $this->trackInventory = (bool) $product->track_inventory;
        $this->lowStockThreshold = (string) $product->low_stock_threshold;
        $this->requiresShipping = (bool) $product->requires_shipping;
        $this->weight = $product->weight !== null ? (string) $product->weight : '';
        $this->seoTitle = (string) $product->seo_title;
        $this->seoDescription = (string) $product->seo_description;
        $this->relatedIds = $product->relatedProducts()->pluck('catalog_products.id')->map(fn ($id) => (int) $id)->all();
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'type' => ['required', 'in:simple,variable,digital'],
            'categoryId' => ['nullable', 'exists:catalog_categories,id'],
            'brandId' => ['nullable', 'exists:catalog_brands,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'compareAtPrice' => ['nullable', 'numeric', 'min:0', 'gt:price'],
            'costPrice' => ['nullable', 'numeric', 'min:0'],
            'sku' => ['nullable', 'string', 'max:100'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'stockQty' => ['required', 'integer', 'min:0'],
            'lowStockThreshold' => ['required', 'integer', 'min:0'],
            'weight' => ['nullable', 'integer', 'min:0'],
            'seoTitle' => ['nullable', 'string', 'max:190'],
            'seoDescription' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function decimals(): int
    {
        return (int) settings('localization.currency_decimals', 2);
    }

    protected function toMinor(string $value): int
    {
        return (int) round(((float) $value) * (10 ** $this->decimals()));
    }

    protected function toMajor(int $minor): string
    {
        return number_format($minor / (10 ** $this->decimals()), $this->decimals(), '.', '');
    }

    protected function attributesForValidation(): array
    {
        return [
            'name' => $this->name, 'type' => $this->type,
            'categoryId' => $this->categoryId, 'brandId' => $this->brandId,
            'price' => $this->price === '' ? null : $this->price,
            'compareAtPrice' => $this->compareAtPrice === '' ? null : $this->compareAtPrice,
            'costPrice' => $this->costPrice === '' ? null : $this->costPrice,
            'sku' => $this->sku, 'barcode' => $this->barcode,
            'stockQty' => $this->stockQty, 'lowStockThreshold' => $this->lowStockThreshold,
            'weight' => $this->weight === '' ? null : $this->weight,
            'seoTitle' => $this->seoTitle, 'seoDescription' => $this->seoDescription,
        ];
    }

    protected function persist(): Product
    {
        $data = [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'type' => $this->type,
            'category_id' => $this->categoryId ?: null,
            'brand_id' => $this->brandId ?: null,
            'price' => $this->toMinor($this->price ?: '0'),
            'compare_at_price' => $this->compareAtPrice !== '' ? $this->toMinor($this->compareAtPrice) : null,
            'cost_price' => $this->costPrice !== '' ? $this->toMinor($this->costPrice) : null,
            'sku' => $this->sku ?: null,
            'barcode' => $this->barcode ?: null,
            'stock_qty' => (int) $this->stockQty,
            // A digital good is delivered as a download — never shipped, never
            // stock-tracked — so enforce that invariant no matter what the form
            // state says (the UI toggles it, but this is the source of truth).
            'track_inventory' => $this->type === 'digital' ? false : $this->trackInventory,
            'low_stock_threshold' => (int) $this->lowStockThreshold,
            // Physical goods (simple & variable) always ship; only digital never does.
            'requires_shipping' => $this->type !== 'digital',
            'weight' => $this->weight !== '' ? (int) $this->weight : null,
            'seo_title' => $this->seoTitle ?: null,
            'seo_description' => $this->seoDescription ?: null,
        ];

        if ($this->productId) {
            $product = Product::findOrFail($this->productId);
            $product->update($data);
            event(new ProductUpdated($product));
        } else {
            $data['status'] = 'draft';
            $product = Product::create($data);
            $this->productId = $product->id;
            $this->slug = $product->slug;
            $this->status = 'draft';
            event(new ProductCreated($product));
        }

        // All variants of a variable product share the product's price — there is no
        // per-variant pricing, so keep every variant in sync with the base price.
        if ($product->type === 'variable') {
            $product->variants()->update([
                'price' => $product->price,
                'compare_at_price' => $product->compare_at_price,
            ]);
        }

        $this->savedAt = now()->format('g:i A');

        return $product;
    }

    /**
     * Switching a product to "digital" means it's delivered as a download —
     * default it to not-shipped and untracked so the owner doesn't have to.
     */
    public function updatedType(): void
    {
        if ($this->type === 'digital') {
            $this->requiresShipping = false;
            $this->trackInventory = false;
        }
    }

    /** Autosave on field blur once the product exists — silent on validation errors. */
    public function updated(string $property): void
    {
        if (! $this->productId || $property === 'mediaUrl' || $property === 'relatedSearch'
            || str_starts_with($property, 'photos')
            || str_starts_with($property, 'digitalUploads')) {
            return;
        }

        if (Validator::make($this->attributesForValidation(), $this->rules())->fails()) {
            return;
        }

        $this->persist();
    }

    public function getAiEnabledProperty(): bool
    {
        return app(AddonRegistry::class)->installed('ai') && app(AiService::class)->featureEnabled('product_copy');
    }

    /**
     * Draft the description, SEO fields and a recommended category with AI.
     * Fills only empty fields so it never clobbers the owner's own copy.
     */
    public function generateWithAi(): void
    {
        if (! app(AddonRegistry::class)->installed('ai')) {
            abort(404);
        }

        $generator = app(ProductContentGenerator::class);

        if (trim($this->name) === '') {
            $this->dispatch('toast', message: 'Enter a product name first', type: 'danger');

            return;
        }

        try {
            $categories = Category::orderBy('name')->pluck('name', 'id');
            $result = $generator->generate($this->name, $categories->values()->all());
        } catch (AiNotConfiguredException $e) {
            $this->dispatch('toast', message: 'Turn on AI in Settings → AI first', type: 'danger');

            return;
        } catch (AiRequestException $e) {
            $this->dispatch('toast', message: 'AI generation failed: '.$e->getMessage(), type: 'danger');

            return;
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: 'AI generation failed. Please try again.', type: 'danger');

            return;
        }

        $items = [];

        if ($result['description'] !== '') {
            $items[] = ['key' => 'description', 'label' => 'Description', 'before' => $this->description, 'after' => $result['description']];
        }
        if ($result['seo_title'] !== '') {
            $items[] = ['key' => 'seo_title', 'label' => 'SEO title', 'before' => $this->seoTitle, 'after' => $result['seo_title']];
        }
        if ($result['seo_description'] !== '') {
            $items[] = ['key' => 'seo_description', 'label' => 'Meta description', 'before' => $this->seoDescription, 'after' => $result['seo_description']];
        }
        if ($result['category'] !== '') {
            $match = $categories->search(fn ($name) => mb_strtolower($name) === mb_strtolower($result['category']));
            if ($match !== false) {
                $items[] = [
                    'key' => 'category',
                    'label' => 'Category',
                    'before' => $this->categoryId ? ($categories[$this->categoryId] ?? '') : '',
                    'after' => $result['category'],
                    'value' => (int) $match,
                ];
            }
        }

        if ($items === []) {
            $this->dispatch('toast', message: 'AI had nothing new to suggest', type: 'danger');

            return;
        }

        $this->openAiReview($items);
    }

    /** Apply whichever AI-suggested fields the owner left checked in the review modal. */
    public function applyAiReview(): void
    {
        foreach ($this->aiReview as $item) {
            if (! $item['selected']) {
                continue;
            }

            match ($item['key']) {
                'description' => $this->description = $item['value'],
                'seo_title' => $this->seoTitle = $item['value'],
                'seo_description' => $this->seoDescription = $item['value'],
                'category' => $this->categoryId = (int) $item['value'],
                default => null,
            };
        }

        $this->discardAiReview();
        $this->dispatch('toast', message: 'AI draft applied — review and edit before saving', type: 'success');
    }

    public function save(): void
    {
        $this->validate();
        $this->persist();
        $this->dispatch('toast', message: $this->productId ? 'Product updated' : 'Draft saved', type: 'success');
    }

    public function publish(): void
    {
        $this->validate();
        $product = $this->persist();
        $product->update(['status' => 'active', 'published_at' => $product->published_at ?? now()]);
        $this->status = 'active';

        event(new ProductPublished($product));
        $this->dispatch('toast', message: 'Product published', type: 'success');
    }

    public function unpublish(): void
    {
        if (! $this->productId) {
            return;
        }

        $product = Product::findOrFail($this->productId);
        $product->update(['status' => 'draft']);
        $this->status = 'draft';
        $this->dispatch('toast', message: 'Moved to draft', type: 'success');
    }

    public function archive(): void
    {
        if (! $this->productId) {
            return;
        }

        $product = Product::findOrFail($this->productId);
        $product->update(['status' => 'archived']);
        $this->status = 'archived';
        event(new ProductArchived($product));
        $this->redirectRoute('admin.catalog.products.index', navigate: true);
    }

    // --- Media (URL-based in S5; Media Manager integration in S6) ---

    public function addMedia(): void
    {
        $url = trim($this->mediaUrl);

        if (! $this->productId || $url === '') {
            return;
        }

        // Only accept real http(s) image URLs — no javascript:/data: or junk (CAT-15).
        if (! preg_match('#^https?://[^\s]+$#i', $url)) {
            $this->addError('mediaUrl', 'Enter a valid http(s) image URL.');

            return;
        }

        $product = Product::findOrFail($this->productId);
        $isFirst = $product->media()->count() === 0;

        $product->media()->create([
            'path' => $url,
            'position' => (int) $product->media()->max('position') + 1,
            'is_featured' => $isFirst,
        ]);

        $this->mediaUrl = '';
    }

    /** Upload images into the media library and attach them to the product. */
    public function updatedPhotos(MediaService $mediaService): void
    {
        if (! $this->productId) {
            return;
        }

        $this->validate(['photos.*' => MediaService::imageRules()]);

        $product = Product::findOrFail($this->productId);

        foreach ($this->photos as $photo) {
            $media = $mediaService->store($photo);
            $isFirst = $product->media()->count() === 0;

            $product->media()->create([
                'media_id' => $media->id,
                'path' => $media->url('medium'),
                'position' => (int) $product->media()->max('position') + 1,
                'is_featured' => $isFirst,
            ]);
        }

        $this->photos = [];
        $this->dispatch('toast', message: 'Images uploaded', type: 'success');
    }

    public function setFeatured(int $mediaId): void
    {
        // Scope to the product being edited so a tampered id can't touch another
        // product's media (CAT-14).
        $product = Product::findOrFail($this->productId);
        $media = $product->media()->whereKey($mediaId)->firstOrFail();
        $product->media()->update(['is_featured' => false]);
        $media->update(['is_featured' => true]);
    }

    public function removeMedia(int $mediaId): void
    {
        ProductMedia::where('product_id', $this->productId)->whereKey($mediaId)->delete();
    }

    // --- Digital assets (downloadable files for a digital product) ---

    /** Store uploaded digital files on the private disk and attach them. */
    public function updatedDigitalUploads(DigitalAssetService $assets): void
    {
        if (! $this->productId) {
            return;
        }

        $this->validate(['digitalUploads.*' => DigitalAssetService::rules()]);

        $product = Product::findOrFail($this->productId);

        foreach ($this->digitalUploads as $upload) {
            $assets->store($product, $upload);
        }

        $this->digitalUploads = [];
        $this->dispatch('toast', message: 'Files uploaded', type: 'success');
    }

    public function removeDigitalFile(int $fileId, DigitalAssetService $assets): void
    {
        // Scope to the product being edited so a tampered id can't touch another
        // product's files (mirrors removeMedia's CAT-14 guard).
        $file = ProductFile::where('product_id', $this->productId)->whereKey($fileId)->firstOrFail();
        $assets->delete($file);
    }

    // --- Recommended / cross-sell products (owner-curated) ---

    public function attachRelated(int $id): void
    {
        if (! $this->productId || $id === $this->productId || in_array($id, $this->relatedIds, true)) {
            return;
        }

        if (! Product::whereKey($id)->exists()) {
            return;
        }

        Product::findOrFail($this->productId)
            ->relatedProducts()
            ->attach($id, ['position' => count($this->relatedIds)]);

        $this->relatedIds[] = $id;
        $this->relatedSearch = '';
    }

    public function detachRelated(int $id): void
    {
        if (! $this->productId) {
            return;
        }

        Product::findOrFail($this->productId)->relatedProducts()->detach($id);
        $this->relatedIds = array_values(array_filter($this->relatedIds, fn ($v) => $v !== $id));
    }

    public function render()
    {
        $media = $this->productId
            ? ProductMedia::where('product_id', $this->productId)->orderBy('position')->get()
            : collect();

        return View::make('catalog::livewire.product-builder', [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
            'media' => $media,
            'digitalFiles' => $this->productId
                ? ProductFile::where('product_id', $this->productId)->orderBy('sort')->orderBy('id')->get()
                : collect(),
            'relatedSelected' => $this->relatedIds !== []
                ? Product::whereIn('id', $this->relatedIds)->get(['id', 'name'])
                : collect(),
            'relatedResults' => ($this->productId && $this->relatedSearch !== '')
                ? Product::where('id', '!=', $this->productId)
                    ->whereNotIn('id', $this->relatedIds)
                    ->where('name', 'like', '%'.$this->relatedSearch.'%')
                    ->orderBy('name')->limit(6)->get(['id', 'name'])
                : collect(),
            'currencySymbol' => settings('localization.currency_symbol', '$'),
        ]);
    }
}
