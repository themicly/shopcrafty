<?php

namespace Themicly\Shopcrafty\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderRefund extends Model
{
    protected $table = 'order_refunds';

    protected $fillable = ['order_id', 'return_id', 'amount', 'reason', 'restocked', 'created_by'];

    protected function casts(): array
    {
        return ['amount' => 'integer', 'restocked' => 'boolean'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
