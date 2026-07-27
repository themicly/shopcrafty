<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A buyer's right to download one product file, created when the order that
 * contains the digital item is paid (confirmed/delivered). Carries optional
 * download-count and expiry limits (null = unlimited) and a running counter.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_download_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('product_file_id')->constrained('catalog_product_files')->cascadeOnDelete();
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedInteger('download_count')->default(0);
            $table->unsignedInteger('max_downloads')->nullable();   // null = unlimited
            $table->timestamp('expires_at')->nullable();            // null = never
            $table->timestamps();

            // One grant per (purchased line, file) — keeps fulfillment idempotent.
            $table->unique(['order_item_id', 'product_file_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_download_grants');
    }
};
