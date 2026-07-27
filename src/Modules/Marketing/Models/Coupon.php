<?php

namespace Themicly\Shopcrafty\Modules\Marketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $code
 * @property string $name
 * @property string $type
 * @property int $value
 * @property string $scope_type
 * @property int|null $buy_qty
 * @property int|null $get_qty
 * @property int|null $min_purchase
 * @property int|null $usage_limit
 * @property int|null $per_customer_limit
 * @property int $used_count
 * @property bool $is_enabled
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 */
class Coupon extends Model
{
    protected $table = 'marketing_coupons';

    // `used_count` is a system-maintained counter — never mass-assignable (MKT-10);
    // it changes only through increment() in the redemption path.
    protected $fillable = [
        'code', 'name', 'type', 'value', 'buy_qty', 'get_qty', 'scope_type', 'scope_ids', 'min_purchase',
        'usage_limit', 'per_customer_limit',
        'starts_at', 'ends_at', 'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'buy_qty' => 'integer',
            'get_qty' => 'integer',
            'scope_ids' => 'array',
            'min_purchase' => 'integer',
            'is_enabled' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function isLive(): bool
    {
        if (! $this->is_enabled) {
            return false;
        }
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }
        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function statusLabel(): string
    {
        if (! $this->is_enabled) {
            return 'disabled';
        }
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'scheduled';
        }
        if ($this->ends_at && $this->ends_at->isPast()) {
            return 'expired';
        }
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return 'used up';
        }

        return 'active';
    }
}
