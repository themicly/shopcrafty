<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stock adjustments were product-only, but a variable product's real stock
 * lives on its variants (the product's stock_qty is just their rolled-up
 * sum) — without this, adjusting a variant's stock had no audit trail.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->foreignId('variant_id')->nullable()->after('product_id')->constrained('catalog_variants')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('variant_id');
        });
    }
};
