<?php

namespace Themicly\Shopcrafty\Modules\Catalog;

use Themicly\Shopcrafty\Core\Module\ModuleServiceProvider;
use Themicly\Shopcrafty\Modules\Catalog\Contracts\CategoryTree;
use Themicly\Shopcrafty\Modules\Catalog\Contracts\ProductFinder;
use Themicly\Shopcrafty\Modules\Catalog\Services\CategoryTreeService;
use Themicly\Shopcrafty\Modules\Catalog\Services\ProductFinderService;

class CatalogServiceProvider extends ModuleServiceProvider
{
    protected function moduleName(): string
    {
        return 'Catalog';
    }

    protected function modulePath(): string
    {
        return __DIR__;
    }

    protected function registerModule(): void
    {
        $this->app->bind(CategoryTree::class, CategoryTreeService::class);
        $this->app->bind(ProductFinder::class, ProductFinderService::class);
    }
}
