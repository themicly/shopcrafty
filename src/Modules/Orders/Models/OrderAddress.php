<?php

namespace Themicly\Shopcrafty\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAddress extends Model
{
    protected $table = 'order_addresses';

    protected $fillable = [
        'order_id', 'type', 'name', 'phone', 'email',
        'address', 'city', 'region', 'postcode', 'location_id',
    ];
}
