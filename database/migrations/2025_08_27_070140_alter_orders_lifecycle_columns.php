<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('orders', function (Blueprint $t) {
            if (!Schema::hasColumn('orders','paid_at')) $t->timestamp('paid_at')->nullable();
            if (!Schema::hasColumn('orders','shipped_at')) $t->timestamp('shipped_at')->nullable();
            if (!Schema::hasColumn('orders','cancelled_at')) $t->timestamp('cancelled_at')->nullable();
            if (!Schema::hasColumn('orders','inventory_committed_at')) $t->timestamp('inventory_committed_at')->nullable();
            $t->index('status');
        });
    }
    public function down(): void {
        Schema::table('orders', function (Blueprint $t) {
            foreach (['paid_at','shipped_at','cancelled_at','inventory_committed_at'] as $c) {
                if (Schema::hasColumn('orders',$c)) $t->dropColumn($c);
            }
        });
    }
};
