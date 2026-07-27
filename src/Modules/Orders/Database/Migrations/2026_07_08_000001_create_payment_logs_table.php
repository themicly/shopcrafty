<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An audit trail of every payment-gateway interaction: session creation,
 * webhooks, return-URL confirmations, and mark-paid reconciliations — with the
 * gateway's own success/error message and a sanitized (secret-free) context.
 *
 * `order_number` is kept alongside the FK so a log line still names its order
 * on screen even after the order itself is deleted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('order_number')->nullable()->index();
            $table->string('gateway')->index();
            $table->string('action')->index();  // create_session | webhook | return_confirm | mark_paid
            $table->boolean('success')->default(false);
            $table->unsignedInteger('http_status')->nullable();
            $table->text('message')->nullable();
            $table->json('context')->nullable();  // sanitized request/response summary (no secrets)
            $table->timestamps();

            $table->index(['gateway', 'action']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
    }
};
