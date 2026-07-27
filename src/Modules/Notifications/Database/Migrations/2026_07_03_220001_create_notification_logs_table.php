<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_key')->index();
            $table->string('channel');
            $table->string('gateway')->nullable();
            $table->string('recipient');
            $table->string('recipient_type')->nullable(); // customer | owner
            $table->string('status')->default('sent');     // sent | failed | skipped
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
