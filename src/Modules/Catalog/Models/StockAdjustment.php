<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    protected $table = 'stock_adjustments';

    protected $fillable = ['product_id', 'variant_id', 'delta', 'before_qty', 'after_qty', 'reason', 'actor'];

    protected function casts(): array
    {
        return ['delta' => 'integer', 'before_qty' => 'integer', 'after_qty' => 'integer'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }
}
