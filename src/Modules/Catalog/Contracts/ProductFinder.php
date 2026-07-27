<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Contracts;

/**
 * Public read surface for products. Other modules (Orders, Marketing, CMS)
 * depend on this contract rather than the Product model directly.
 * Implemented in Session 5.
 */
interface ProductFinder
{
    public function findById(int $id): ?object;

    public function findBySlug(string $slug): ?object;

    /** @return iterable<int, object> */
    public function search(string $term, int $limit = 10): iterable;
}
