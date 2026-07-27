<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Campaigns could only ever go to the full subscriber list. This records
 * which customer tags a campaign was targeted at (null = the full subscriber
 * list, unchanged default), so the composer can offer a narrower audience
 * and the send history shows who actually got it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('newsletter_campaigns', function (Blueprint $table) {
            $table->json('audience_tags')->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('newsletter_campaigns', function (Blueprint $table) {
            $table->dropColumn('audience_tags');
        });
    }
};
