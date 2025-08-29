<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->boolean('is_primary')->default(false)->index();
        });
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS product_images_one_primary_per_product ON product_images (product_id) WHERE is_primary = true;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            DB::statement('DROP INDEX IF EXISTS product_images_one_primary_per_product;');
            Schema::table('product_images', function (Blueprint $table) {
                $table->dropColumn('is_primary');
            });
        });
    }
};
