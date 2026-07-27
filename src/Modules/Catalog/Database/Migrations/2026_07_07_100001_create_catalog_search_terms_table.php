<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aggregated storefront search analytics. One row per normalized term
 * (lowercased, whitespace-collapsed, capped at 120 chars) with a running
 * popularity counter — recorded only for submitted searches, never for
 * keystroke suggestions (see SearchTermRecorder).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_search_terms', function (Blueprint $table) {
            $table->id();
            $table->string('term', 120)->unique();
            $table->unsignedBigInteger('count')->default(1);
            $table->timestamp('last_searched_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_search_terms');
    }
};
