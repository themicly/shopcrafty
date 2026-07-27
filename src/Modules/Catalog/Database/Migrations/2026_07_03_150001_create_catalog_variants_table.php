<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Which attributes/values drive a product's variants (persisted so the
        // generator survives reloads): [{ "attribute_id": 1, "value_ids": [1,2] }]
        Schema::table('catalog_products', function (Blueprint $table) {
            $table->json('variant_config')->nullable()->after('type');
        });

        Schema::create('catalog_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('catalog_products')->cascadeOnDelete();
            $table->json('options'); // { "Color": "Red", "Size": "M" }
            $table->string('options_key')->index(); // canonical "color:red|size:m" for dedupe
            $table->string('sku')->nullable();
            $table->unsignedBigInteger('price')->default(0);
            $table->unsignedBigInteger('compare_at_price')->nullable();
            $table->integer('stock_qty')->default(0);
            $table->unsignedBigInteger('image_id')->nullable(); // product_media id
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_variants');

        Schema::table('catalog_products', function (Blueprint $table) {
            $table->dropColumn('variant_config');
        });
    }
};
