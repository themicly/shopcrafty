<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $product_id
 * @property array $options
 * @property string $options_key
 * @property string|null $sku
 * @property int $price
 * @property int|null $compare_at_price
 * @property int $stock_qty
 * @property int|null $image_id
 * @property int $position
 */
class Variant extends Model
{
    protected $table = 'catalog_variants';

    protected $fillable = [
        'product_id', 'options', 'options_key', 'sku',
        'price', 'compare_at_price', 'stock_qty', 'image_id', 'position',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'price' => 'integer',
            'compare_at_price' => 'integer',
            'stock_qty' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Canonical key from an options map, e.g. ['Color'=>'Red'] => "color:red". */
    public static function keyFor(array $options): string
    {
        $parts = [];
        foreach ($options as $attr => $value) {
            $parts[] = strtolower($attr).':'.strtolower($value);
        }
        sort($parts);

        return implode('|', $parts);
    }
}
