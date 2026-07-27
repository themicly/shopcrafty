<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Services;

use Illuminate\Support\Collection;
use Themicly\Shopcrafty\Modules\Catalog\Contracts\CategoryTree;
use Themicly\Shopcrafty\Modules\Catalog\Models\Category;

class CategoryTreeService implements CategoryTree
{
    public function roots(): Collection
    {
        return Category::query()
            ->whereNull('parent_id')
            ->with('children.children.children')
            ->orderBy('position')
            ->orderBy('name')
            ->get();
    }

    public function flat(): Collection
    {
        // Depth-labelled flat list for parent-select dropdowns.
        $out = collect();

        $walk = function ($categories, $depth) use (&$walk, $out) {
            foreach ($categories as $category) {
                $out->push((object) [
                    'id' => $category->id,
                    'label' => str_repeat('— ', $depth).$category->name,
                    'depth' => $depth,
                ]);
                $walk($category->children, $depth + 1);
            }
        };

        $walk($this->roots(), 0);

        return $out;
    }
}
