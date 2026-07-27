<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Downloadable assets attached to a digital product. Files live on a PRIVATE
 * disk (storage/app/private) — never web-reachable; access is brokered through
 * order-scoped download grants (see order_download_grants).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_product_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('catalog_products')->cascadeOnDelete();
            $table->string('name');            // original filename shown to the buyer
            $table->string('disk')->default('local');
            $table->string('path');            // path on the private disk
            $table->unsignedBigInteger('size')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_product_files');
    }
};
