<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracks whether an order has decremented inventory. COD orders reserve stock
 * only when confirmed (not at placement), so we need to know if a given order
 * has already committed its stock — to avoid double-decrement and to restock on
 * cancellation/return.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('stock_committed')->default(false)->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('stock_committed');
        });
    }
};
