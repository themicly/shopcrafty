<?php

/*
|--------------------------------------------------------------------------
| Catalog — Admin Routes
|--------------------------------------------------------------------------
| Loaded under the "web" + "auth" group with an "/admin" prefix and "admin."
| name prefix by CatalogServiceProvider.
*/

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Themicly\Shopcrafty\Modules\Catalog\Services\ProductCsv;

Route::prefix('catalog')->name('catalog.')->middleware('can:manage-products')->group(function () {
    Route::view('/products', 'admin.catalog.products.index')->name('products.index');
    Route::get('/products/export', function (ProductCsv $csv) {
        $content = $csv->export();

        return response()->streamDownload(fn () => print ($content), 'products-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    })->name('products.export');
    Route::view('/products/import', 'admin.catalog.products.import')->name('products.import');
    Route::get('/products/import/sample', function () {
        $rows = [
            ProductCsv::COLUMNS,
            ['TSHIRT-01', 'Classic Tee', 'Soft everyday cotton tee', 'Apparel', 'Aura', '19.99', '29.99', '8.00', '100', 'yes', '5', 'active', '200', 'Classic Tee', 'A soft everyday tee'],
            ['MUG-01', 'Ceramic Mug', '', 'Home & Living', '', '12.50', '', '', '50', 'yes', '5', 'draft', '', '', ''],
        ];

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, 'products-sample.csv', ['Content-Type' => 'text/csv']);
    })->name('products.import.sample');
    Route::view('/products/create', 'admin.catalog.products.create')->name('products.create');
    Route::get('/products/{product}/edit', fn ($product) => View::make('admin.catalog.products.edit', ['productId' => (int) $product]))
        ->name('products.edit')
        ->whereNumber('product');

    Route::view('/categories', 'admin.catalog.categories')->name('categories.index');
    Route::view('/brands', 'admin.catalog.brands')->name('brands.index');
    Route::view('/inventory', 'admin.catalog.inventory')->name('inventory.index');
    Route::view('/attributes', 'admin.catalog.attributes')->name('attributes.index');
});
