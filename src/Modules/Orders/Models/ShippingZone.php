<?php

namespace Themicly\Shopcrafty\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property int $rate
 * @property int|null $free_above
 * @property bool $is_active
 * @property int $position
 */
class ShippingZone extends Model
{
    protected $table = 'shipping_zones';

    protected $fillable = ['name', 'rate', 'free_above', 'is_active', 'position'];

    protected function casts(): array
    {
        return ['rate' => 'integer', 'free_above' => 'integer', 'is_active' => 'boolean'];
    }

    public function costFor(int $subtotal): int
    {
        if ($this->free_above !== null && $subtotal >= $this->free_above) {
            return 0;
        }

        return (int) $this->rate;
    }
}
