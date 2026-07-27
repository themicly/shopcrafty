<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->nullable();
            $table->string('type'); // percentage | fixed | free_shipping
            $table->unsignedBigInteger('value')->default(0); // percent (0-100) or minor amount
            $table->unsignedBigInteger('min_purchase')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('per_customer_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('marketing_coupon_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('marketing_coupons')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->timestamps();
        });

        // "Frequently bought together" — co-occurrence weights recomputed from order history.
        Schema::create('marketing_product_pairs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('paired_product_id');
            $table->unsignedInteger('weight')->default(1);
            $table->timestamps();
            $table->unique(['product_id', 'paired_product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_product_pairs');
        Schema::dropIfExists('marketing_coupon_redemptions');
        Schema::dropIfExists('marketing_coupons');
    }
};
