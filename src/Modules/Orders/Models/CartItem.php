<?php

namespace Themicly\Shopcrafty\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\Catalog\Models\Variant;

/**
 * @property int $product_id
 * @property int|null $variant_id
 * @property int $qty
 */
class CartItem extends Model
{
    protected $table = 'cart_items';

    protected $fillable = ['cart_id', 'product_id', 'variant_id', 'qty'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    public function unitPrice(): int
    {
        return (int) ($this->variant?->price ?? $this->product?->price ?? 0);
    }

    public function lineTotal(): int
    {
        return $this->unitPrice() * $this->qty;
    }
}
