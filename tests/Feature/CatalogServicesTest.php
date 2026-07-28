<?php

use Themicly\Shopcrafty\Modules\Catalog\Models\Category;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\Catalog\Services\CategoryTreeService;
use Themicly\Shopcrafty\Modules\Catalog\Services\ProductCsv;
use Themicly\Shopcrafty\Modules\Catalog\Services\ProductFinderService;
use Themicly\Shopcrafty\Modules\Catalog\Services\RecentlyViewed;
use Themicly\Shopcrafty\Tests\TestCase;

final class CatalogServicesTest extends TestCase
{
    protected function migrateCore(): void
    {
        $this->artisan('migrate')->assertExitCode(0);
    }

    public function test_category_tree_and_product_finder_return_catalogue_data(): void
    {
        $this->migrateCore();
        $root = Category::create(['name' => 'Clothing', 'position' => 1, 'is_active' => true]);
        $child = Category::create(['name' => 'Shirts', 'parent_id' => $root->id, 'position' => 1, 'is_active' => true]);
        $product = Product::create(['name' => 'Oxford Shirt', 'price' => 2500, 'status' => 'active']);

        $tree = app(CategoryTreeService::class);
        $finder = app(ProductFinderService::class);

        $this->assertSame([$root->id], $tree->roots()->modelKeys());
        $this->assertSame(['Clothing', '— Shirts'], $tree->flat()->pluck('label')->all());
        $this->assertSame($product->id, $finder->findBySlug('oxford-shirt')?->id);
        $this->assertSame($product->id, $finder->search('Oxford')->first()?->id);
        $this->assertSame($child->id, $root->fresh()->children->first()->id);
    }

    public function test_recently_viewed_is_deduplicated_capped_and_ordered(): void
    {
        $service = app(RecentlyViewed::class);

        foreach (range(1, 10) as $id) {
            $service->record($id);
        }
        $service->record(5);

        $this->assertCount(8, $service->ids());
        $this->assertSame(5, $service->ids()[0]);
        $this->assertCount(8, array_keys(array_flip($service->ids())));
    }

    public function test_product_csv_imports_creates_categories_and_exports_safe_values(): void
    {
        $this->migrateCore();
        $csv = "sku,name,description,category,brand,price,compare_at_price,cost_price,stock_qty,track_inventory,low_stock_threshold,status,weight,seo_title,seo_description\n".
            "SKU-1,=Formula,Description,Shirts,Acme,12.50,20.00,5.00,4,yes,1,active,100,,\n";

        $result = app(ProductCsv::class)->import($csv);
        $export = app(ProductCsv::class)->export();

        $this->assertSame(1, $result['created']);
        $this->assertSame([], $result['errors']);
        $this->assertDatabaseHas('catalog_products', ['sku' => 'SKU-1', 'price' => 1250]);
        $this->assertStringContainsString("'=Formula", $export);
    }
}
