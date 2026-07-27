<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Owner-curated "recommended / bought with" products per product. When present,
 * these override the automatic co-occurrence recommendations on the storefront.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_product_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('catalog_products')->cascadeOnDelete();
            $table->foreignId('related_product_id')->constrained('catalog_products')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'related_product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_product_relations');
    }
};
