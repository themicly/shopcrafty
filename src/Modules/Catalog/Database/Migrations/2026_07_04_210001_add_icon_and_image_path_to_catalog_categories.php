<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalog_categories', function (Blueprint $table) {
            $table->string('icon')->nullable()->after('description');
            $table->string('image_path')->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('catalog_categories', function (Blueprint $table) {
            $table->dropColumn(['icon', 'image_path']);
        });
    }
};
