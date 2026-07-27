<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Shipment tracking so "mark shipped" can carry a carrier + tracking number. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('carrier')->nullable()->after('coupon_code');
            $table->string('tracking_number')->nullable()->after('carrier');
            $table->timestamp('shipped_at')->nullable()->after('tracking_number');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['carrier', 'tracking_number', 'shipped_at']);
        });
    }
};
