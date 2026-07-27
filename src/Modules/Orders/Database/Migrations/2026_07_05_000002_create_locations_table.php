<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A generic, admin-managed location tree (Division → District → Area, or any
 * country's own hierarchy). Levels are arbitrary depth so it works for any
 * country; each node can map to a shipping zone so checkout derives the rate
 * from the chosen address instead of the shopper picking a zone by hand.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('locations')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('level')->default(0); // 0-based depth
            $table->foreignId('shipping_zone_id')->nullable()->constrained('shipping_zones')->nullOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['parent_id', 'level']);
        });

        // Remember the chosen node on the address for fulfilment / analytics.
        Schema::table('order_addresses', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->nullable()->after('region');
        });
    }

    public function down(): void
    {
        Schema::table('order_addresses', function (Blueprint $table) {
            $table->dropColumn('location_id');
        });

        Schema::dropIfExists('locations');
    }
};
