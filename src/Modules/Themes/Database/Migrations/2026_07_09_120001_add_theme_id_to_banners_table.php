<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Banners are otherwise theme-agnostic (any theme's slider shows every live
 * "home_slider" banner), which mixes verticals once more than one theme has
 * its own demo content. A nullable theme_id lets a banner opt into showing
 * only under one theme, while existing (theme_id = null) banners keep
 * showing everywhere, unchanged.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->foreignId('theme_id')->nullable()->after('placement')->constrained('themes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropConstrainedForeignId('theme_id');
        });
    }
};
