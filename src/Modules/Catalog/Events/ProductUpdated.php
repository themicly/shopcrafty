<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;

class ProductUpdated
{
    use Dispatchable;

    public function __construct(public Product $product) {}
}
