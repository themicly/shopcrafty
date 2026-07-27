<?php

namespace Themicly\Shopcrafty\Modules\Marketing\Models;

use Illuminate\Database\Eloquent\Model;

class CouponRedemption extends Model
{
    protected $table = 'marketing_coupon_redemptions';

    protected $fillable = ['coupon_id', 'order_id', 'customer_id', 'discount_amount'];
}
