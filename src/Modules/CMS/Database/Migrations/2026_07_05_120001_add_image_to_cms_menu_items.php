<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_menu_items', function (Blueprint $table) {
            // Optional image for mega-menu tiles (falls back to the linked category image).
            $table->string('image')->nullable()->after('url');
        });
    }

    public function down(): void
    {
        Schema::table('cms_menu_items', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
