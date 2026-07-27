<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('refunded_total')->default(0)->after('grand_total');
        });

        // Customer-initiated return requests.
        Schema::create('order_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->string('status')->default('requested'); // requested | approved | rejected
            $table->text('admin_note')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        // Refund records (from an approved return, or issued directly by admin).
        Schema::create('order_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('return_id')->nullable()->constrained('order_returns')->nullOnDelete();
            $table->unsignedBigInteger('amount')->default(0); // minor units
            $table->string('reason')->nullable();
            $table->boolean('restocked')->default(false);
            $table->unsignedBigInteger('created_by')->nullable(); // admin user id
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_refunds');
        Schema::dropIfExists('order_returns');
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('refunded_total');
        });
    }
};
