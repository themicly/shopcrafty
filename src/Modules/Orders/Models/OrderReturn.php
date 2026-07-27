<?php

namespace Themicly\Shopcrafty\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $order_id
 * @property string $reason
 * @property string $status
 * @property Carbon|null $resolved_at
 * @property Carbon|null $received_at
 * @property Carbon|null $refunded_at
 * @property Carbon|null $created_at
 */
class OrderReturn extends Model
{
    protected $table = 'order_returns';

    /** Lifecycle a return moves through. */
    public const STATUSES = ['requested', 'approved', 'rejected', 'received', 'refunded'];

    protected $fillable = [
        'order_id', 'customer_id', 'reason', 'photos', 'status',
        'admin_note', 'resolved_at', 'received_at', 'refunded_at',
    ];

    protected function casts(): array
    {
        return [
            'photos' => 'array',
            'resolved_at' => 'datetime',
            'received_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderReturnItem::class, 'return_id');
    }

    public function isOpen(): bool
    {
        return $this->status === 'requested';
    }

    /**
     * The refund this return is worth — the price × returned qty of each line.
     * Falls back to the order's full refundable amount for a return that has no
     * structured line items.
     */
    public function computedRefund(): int
    {
        $this->loadMissing('items.orderItem');

        $total = (int) $this->items->sum(
            fn (OrderReturnItem $line) => (int) ($line->orderItem?->price ?? 0) * min((int) $line->qty, (int) ($line->orderItem?->qty ?? 0)),
        );

        return $total > 0 ? $total : $this->order->refundableAmount();
    }

    /**
     * Ordered status events for a timeline, each with its timestamp (null =
     * not yet reached). "Resolved" covers approved/rejected (resolved_at).
     *
     * @return array<int, array{key: string, label: string, at: ?Carbon}>
     */
    public function timeline(): array
    {
        $resolvedLabel = $this->status === 'rejected' ? 'Rejected' : 'Approved';

        return [
            ['key' => 'requested', 'label' => 'Requested', 'at' => $this->created_at],
            ['key' => 'resolved', 'label' => $resolvedLabel, 'at' => $this->resolved_at],
            ['key' => 'received', 'label' => 'Received', 'at' => $this->received_at],
            ['key' => 'refunded', 'label' => 'Refunded', 'at' => $this->refunded_at],
        ];
    }
}
