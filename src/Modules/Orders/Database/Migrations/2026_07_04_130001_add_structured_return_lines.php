<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extends the (flat) return request into a structured RMA: which items + how many
 * are being returned, optional photo evidence, and per-status timestamps so the
 * customer can follow the request through requested → received → refunded.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_returns', function (Blueprint $table) {
            $table->json('photos')->nullable()->after('reason');       // uploaded evidence paths
            $table->timestamp('received_at')->nullable()->after('resolved_at');
            $table->timestamp('refunded_at')->nullable()->after('received_at');
        });

        // One row per returned line: which order item and how many units.
        Schema::create('order_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained('order_returns')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->unsignedInteger('qty')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_return_items');

        Schema::table('order_returns', function (Blueprint $table) {
            $table->dropColumn(['photos', 'received_at', 'refunded_at']);
        });
    }
};
