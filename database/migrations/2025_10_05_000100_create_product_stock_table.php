<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->integer('qty')->default(0);
            $table->integer('reserved')->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'warehouse_id']);
        });

        $warehouseId = DB::table('warehouses')->where('code', 'MAIN')->value('id');

        if ($warehouseId) {
            $products = DB::table('products')->select('id', 'stock')->get();

            foreach ($products as $product) {
                DB::table('product_stocks')->insert([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouseId,
                    'qty' => (int) ($product->stock ?? 0),
                    'reserved' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_stocks');
    }
};
