<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Themicly\Shopcrafty\Core\Concerns\HasSlug;

/**
 * @property string $name
 * @property string $slug
 * @property string $type
 * @property string $status
 * @property string|null $description
 * @property int|null $category_id
 * @property int|null $brand_id
 * @property int $price
 * @property int|null $cost_price
 * @property int|null $compare_at_price
 * @property string|null $sku
 * @property string|null $barcode
 * @property int $stock_qty
 * @property int $low_stock_threshold
 * @property bool $track_inventory
 * @property bool $requires_shipping
 * @property int|null $weight
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property Carbon|null $published_at
 */
class Product extends Model
{
    use HasSlug;

    protected $table = 'catalog_products';

    protected $fillable = [
        'name', 'slug', 'type', 'status', 'description',
        'category_id', 'brand_id', 'variant_config',
        'price', 'compare_at_price', 'cost_price',
        'sku', 'barcode', 'stock_qty', 'track_inventory', 'low_stock_threshold',
        'weight', 'requires_shipping',
        'seo_title', 'seo_description', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'compare_at_price' => 'integer',
            'cost_price' => 'integer',
            'stock_qty' => 'integer',
            'track_inventory' => 'boolean',
            'requires_shipping' => 'boolean',
            'published_at' => 'datetime',
            'variant_config' => 'array',
        ];
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class)->orderBy('position');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class)->orderBy('position');
    }

    /** Downloadable assets — only meaningful for digital products. */
    public function files(): HasMany
    {
        return $this->hasMany(ProductFile::class)->orderBy('sort')->orderBy('id');
    }

    /** A digital good is delivered as a download, not shipped. */
    public function isDigital(): bool
    {
        return $this->type === 'digital';
    }

    /** Owner-curated "recommended / bought with" products (overrides auto FBT). */
    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'catalog_product_relations', 'product_id', 'related_product_id')
            ->withPivot('position')
            ->orderBy('catalog_product_relations.position');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function isLowStock(): bool
    {
        return $this->track_inventory && $this->stock_qty <= $this->low_stock_threshold;
    }

    public function featuredImage(): ?ProductMedia
    {
        return $this->media->firstWhere('is_featured', true) ?? $this->media->first();
    }
}
