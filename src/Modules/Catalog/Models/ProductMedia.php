<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $product_id
 * @property int|null $media_id
 * @property string $path
 * @property int $position
 * @property bool $is_featured
 */
class ProductMedia extends Model
{
    protected $table = 'catalog_product_media';

    protected $fillable = ['product_id', 'media_id', 'path', 'position', 'is_featured'];

    protected function casts(): array
    {
        return ['is_featured' => 'boolean'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
