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

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS product_images_one_primary_per_product ON product_images (product_id) WHERE is_primary = true;');

            return;
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            Schema::table('product_images', function (Blueprint $table) {
                $table->unsignedBigInteger('primary_product_id')
                    ->nullable()
                    ->storedAs('CASE WHEN is_primary = 1 THEN product_id ELSE NULL END');
            });

            Schema::table('product_images', function (Blueprint $table) {
                $table->unique('primary_product_id', 'product_images_one_primary_per_product');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS product_images_one_primary_per_product;');
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            Schema::table('product_images', function (Blueprint $table) {
                $table->dropUnique('product_images_one_primary_per_product');
                $table->dropColumn('primary_product_id');
            });
        }

        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn('is_primary');
        });
    }
};
