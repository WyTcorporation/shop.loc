<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'warehouse_id')) {
                $table->foreignId('warehouse_id')
                    ->nullable()
                    ->after('product_id')
                    ->constrained('warehouses')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'warehouse_id')) {
                $table->dropForeign(['warehouse_id']);
                $table->dropColumn('warehouse_id');
            }
        });
    }
};
