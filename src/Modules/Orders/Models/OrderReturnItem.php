<?php

namespace Themicly\Shopcrafty\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $return_id
 * @property int $order_item_id
 * @property int $qty
 */
class OrderReturnItem extends Model
{
    protected $table = 'order_return_items';

    protected $fillable = ['return_id', 'order_item_id', 'qty'];

    protected function casts(): array
    {
        return ['qty' => 'integer'];
    }

    public function return(): BelongsTo
    {
        return $this->belongsTo(OrderReturn::class, 'return_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }
}
