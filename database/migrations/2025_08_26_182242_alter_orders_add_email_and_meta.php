<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $t) {
            if (!Schema::hasColumn('orders', 'email')) {
                $t->string('email')->after('user_id');
                $t->index('email');
            }
            if (!Schema::hasColumn('orders', 'number')) {
                $t->string('number')->after('email');
                $t->unique('number');
            }
            if (!Schema::hasColumn('orders', 'status')) {
                $t->string('status')->default('new');
            }
            if (!Schema::hasColumn('orders', 'total')) {
                $t->decimal('total', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('orders', 'shipping_address')) {
                $t->json('shipping_address')->nullable();
            }
            if (!Schema::hasColumn('orders', 'billing_address')) {
                $t->json('billing_address')->nullable();
            }
            if (!Schema::hasColumn('orders', 'note')) {
                $t->text('note')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $t) {
            if (Schema::hasColumn('orders', 'note')) $t->dropColumn('note');
            if (Schema::hasColumn('orders', 'billing_address')) $t->dropColumn('billing_address');
            if (Schema::hasColumn('orders', 'shipping_address')) $t->dropColumn('shipping_address');
            if (Schema::hasColumn('orders', 'total')) $t->dropColumn('total');
            if (Schema::hasColumn('orders', 'status')) $t->dropColumn('status');
            if (Schema::hasColumn('orders', 'number')) { $t->dropUnique(['number']); $t->dropColumn('number'); }
            if (Schema::hasColumn('orders', 'email')) { $t->dropIndex(['email']); $t->dropColumn('email'); }
        });
    }
};
