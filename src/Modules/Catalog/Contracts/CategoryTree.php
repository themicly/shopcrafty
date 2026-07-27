<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Contracts;

use Illuminate\Support\Collection;
use Themicly\Shopcrafty\Modules\Catalog\Models\Category;

interface CategoryTree
{
    /**
     * Root categories with nested children eager-loaded, ordered by position.
     *
     * @return Collection<int, Category>
     */
    public function roots(): Collection;

    /** Flat list of all categories, ordered for a parent-select dropdown. */
    public function flat(): Collection;
}
