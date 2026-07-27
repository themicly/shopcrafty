<?php

namespace Themicly\Shopcrafty\Modules\Marketing\Models;

use Illuminate\Database\Eloquent\Model;

/** @property int $weight */
class ProductPair extends Model
{
    protected $table = 'marketing_product_pairs';

    protected $fillable = ['product_id', 'paired_product_id', 'weight'];
}
