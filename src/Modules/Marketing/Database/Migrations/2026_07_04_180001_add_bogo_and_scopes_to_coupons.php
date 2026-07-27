<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** BOGO (buy X get Y) coupon type + category/product scoping. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_coupons', function (Blueprint $table) {
            $table->unsignedInteger('buy_qty')->nullable()->after('value');
            $table->unsignedInteger('get_qty')->nullable()->after('buy_qty');
            $table->string('scope_type')->default('all')->after('get_qty'); // all | category | product
            $table->json('scope_ids')->nullable()->after('scope_type');
        });
    }

    public function down(): void
    {
        Schema::table('marketing_coupons', function (Blueprint $table) {
            $table->dropColumn(['buy_qty', 'get_qty', 'scope_type', 'scope_ids']);
        });
    }
};
