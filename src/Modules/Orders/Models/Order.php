<?php

namespace Themicly\Shopcrafty\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property string $number
 * @property int|null $customer_id
 * @property string $status
 * @property string $payment_status
 * @property string|null $payment_method
 * @property int $discount_total
 * @property int $grand_total
 * @property int $revenue
 * @property int $refunded_total
 * @property string|null $coupon_code
 * @property string|null $method
 * @property Carbon|null $placed_at
 * @property bool $stock_committed
 * @property-read Collection $orders
 */
class Order extends Model
{
    protected $table = 'orders';

    /**
     * Order statuses that count toward booked revenue / GMV. Single source of
     * truth so every report screen agrees (QA RPT-04).
     */
    public const REVENUE_STATUSES = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];

    protected $fillable = [
        'number', 'customer_id', 'status', 'payment_status', 'stock_committed', 'payment_method',
        'cod_verification_status', 'subtotal', 'discount_total', 'shipping_total', 'tax_total',
        'grand_total', 'refunded_total', 'currency', 'coupon_code', 'carrier', 'tracking_number', 'shipped_at',
        'notes', 'source', 'placed_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'discount_total' => 'integer',
            'shipping_total' => 'integer',
            'tax_total' => 'integer',
            'grand_total' => 'integer',
            'refunded_total' => 'integer',
            'stock_committed' => 'boolean',
            'placed_at' => 'datetime',
            'shipped_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(OrderAddress::class);
    }

    public function shippingAddress(): HasOne
    {
        return $this->hasOne(OrderAddress::class)->where('type', 'shipping');
    }

    public function history(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest();
    }

    public function returns(): HasMany
    {
        return $this->hasMany(OrderReturn::class)->latest();
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(OrderRefund::class)->latest();
    }

    /** Money actually collected for this order (0 until the order is paid). */
    public function capturedAmount(): int
    {
        return in_array($this->payment_status, ['paid', 'partially_refunded', 'refunded'], true)
            ? (int) $this->grand_total
            : 0;
    }

    /**
     * Amount still refundable — what was captured minus what's already been
     * refunded. An unpaid order (e.g. pending COD) has nothing to refund.
     */
    public function refundableAmount(): int
    {
        return max(0, $this->capturedAmount() - (int) $this->refunded_total);
    }
}
