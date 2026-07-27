<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('simple'); // simple | variable | digital
            $table->string('status')->default('draft')->index(); // draft | active | archived
            $table->text('description')->nullable();

            $table->foreignId('category_id')->nullable()->constrained('catalog_categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('catalog_brands')->nullOnDelete();

            // Money in minor units (poisha/cents).
            $table->unsignedBigInteger('price')->default(0);
            $table->unsignedBigInteger('compare_at_price')->nullable();
            $table->unsignedBigInteger('cost_price')->nullable();

            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();

            $table->integer('stock_qty')->default(0);
            $table->boolean('track_inventory')->default(true);
            $table->unsignedInteger('low_stock_threshold')->default(0);

            $table->unsignedInteger('weight')->nullable(); // grams
            $table->boolean('requires_shipping')->default(true);

            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();

            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'category_id']);
        });

        Schema::create('catalog_product_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('catalog_products')->cascadeOnDelete();
            $table->unsignedBigInteger('media_id')->nullable(); // links to media library in Session 6
            $table->string('path');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        Schema::create('catalog_product_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('catalog_products')->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('count')->default(0);
            $table->unique(['product_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_product_views');
        Schema::dropIfExists('catalog_product_media');
        Schema::dropIfExists('catalog_products');
    }
};
