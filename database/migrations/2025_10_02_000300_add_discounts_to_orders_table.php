<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->string('coupon_code')->nullable();
            $table->decimal('coupon_discount', 12, 2)->default(0);
            $table->integer('loyalty_points_used')->default(0);
            $table->decimal('loyalty_points_value', 12, 2)->default(0);
            $table->integer('loyalty_points_earned')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal',
                'discount_total',
                'coupon_code',
                'coupon_discount',
                'loyalty_points_used',
                'loyalty_points_value',
                'loyalty_points_earned',
            ]);
            $table->dropConstrainedForeignId('coupon_id');
        });
    }
};
