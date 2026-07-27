<?php

namespace Themicly\Shopcrafty\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;

/**
 * @property int $order_id
 * @property int|null $product_id
 * @property int|null $variant_id
 * @property string $name
 * @property string|null $variant_label
 * @property string|null $sku
 * @property string|null $license_key
 * @property int $price
 * @property int $qty
 * @property int $line_total
 */
class OrderItem extends Model
{
    protected $table = 'order_items';

    protected $fillable = [
        'order_id', 'product_id', 'variant_id', 'name', 'variant_label',
        'sku', 'license_key', 'price', 'qty', 'line_total',
    ];

    protected function casts(): array
    {
        return ['price' => 'integer', 'qty' => 'integer', 'line_total' => 'integer'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function downloadGrants(): HasMany
    {
        return $this->hasMany(DownloadGrant::class, 'order_item_id');
    }
}
