<?php

namespace Themicly\Shopcrafty\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $table = 'carts';

    protected $fillable = ['token', 'customer_id', 'email', 'reminded_at', 'reminder_count', 'recovered_at'];

    protected function casts(): array
    {
        return ['reminded_at' => 'datetime', 'reminder_count' => 'integer', 'recovered_at' => 'datetime'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
