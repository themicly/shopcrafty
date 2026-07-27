<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $disk
 * @property string $path
 * @property int $size
 */
class ProductFile extends Model
{
    protected $table = 'catalog_product_files';

    protected $fillable = ['product_id', 'name', 'disk', 'path', 'size', 'sort'];

    protected function casts(): array
    {
        return ['size' => 'integer', 'sort' => 'integer'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Human-friendly file size, e.g. "2.4 MB". */
    public function humanSize(): string
    {
        $bytes = (int) $this->size;

        if ($bytes <= 0) {
            return '';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);

        return round($bytes / (1024 ** $power), 1).' '.$units[$power];
    }
}
