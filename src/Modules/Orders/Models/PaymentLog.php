<?php

namespace Themicly\Shopcrafty\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row per payment-gateway interaction. `context` is a sanitized summary of
 * the request/response (never secrets — see PaymentLogger). `order_number` is
 * retained for display even if the order is later deleted (order_id nullOnDelete).
 */
class PaymentLog extends Model
{
    protected $table = 'payment_logs';

    protected $fillable = [
        'order_id', 'order_number', 'gateway', 'action',
        'success', 'http_status', 'message', 'context',
    ];

    protected function casts(): array
    {
        return [
            'success' => 'boolean',
            'context' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
