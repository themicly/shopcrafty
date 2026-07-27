<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Services;

use Themicly\Shopcrafty\Modules\Catalog\Contracts\ProductFinder;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;

class ProductFinderService implements ProductFinder
{
    public function findById(int $id): ?object
    {
        return Product::find($id);
    }

    public function findBySlug(string $slug): ?object
    {
        return Product::where('slug', $slug)->first();
    }

    public function search(string $term, int $limit = 10): iterable
    {
        return Product::query()
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%");
            })
            ->limit($limit)
            ->get();
    }
}
