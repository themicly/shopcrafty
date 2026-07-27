<?php

namespace Themicly\Shopcrafty\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Themicly\Shopcrafty\Modules\Catalog\Models\ProductFile;

/**
 * A buyer's right to download one product file. Created on payment; consumed
 * (counted) each time the file is served.
 */
/**
 * @property int $order_id
 * @property int $order_item_id
 * @property int $product_file_id
 * @property int $product_id
 * @property int $download_count
 * @property int $max_downloads
 * @property Carbon|null $expires_at
 */
class DownloadGrant extends Model
{
    protected $table = 'order_download_grants';

    protected $fillable = [
        'order_id', 'order_item_id', 'product_file_id', 'product_id',
        'download_count', 'max_downloads', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'download_count' => 'integer',
            'max_downloads' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(ProductFile::class, 'product_file_id');
    }

    /** Whether this grant may still be downloaded (within limit and unexpired). */
    public function isDownloadable(): bool
    {
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return $this->max_downloads === null || $this->download_count < $this->max_downloads;
    }
}
