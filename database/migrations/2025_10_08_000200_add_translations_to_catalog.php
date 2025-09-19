<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $locale = config('app.locale');

        Schema::table('categories', function (Blueprint $table) {
            $table->json('name_translations')->nullable()->after('name');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->json('name_translations')->nullable()->after('name');
        });

        if (Schema::hasColumn('products', 'description')
            && ! Schema::hasColumn('products', 'description_translations')) {
            Schema::table('products', function (Blueprint $table) {
                $table->json('description_translations')->nullable();
            });
        }

        Schema::table('vendors', function (Blueprint $table) {
            $table->json('name_translations')->nullable()->after('name');
            $table->json('description_translations')->nullable()->after('description');
        });

        Schema::table('warehouses', function (Blueprint $table) {
            $table->json('name_translations')->nullable()->after('name');
            $table->json('description_translations')->nullable()->after('description');
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->json('name_translations')->nullable()->after('name');
            $table->json('description_translations')->nullable()->after('description');
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->json('alt_translations')->nullable()->after('alt');
        });

        $this->copyExistingValues('categories', 'name', 'name_translations', $locale);
        $this->copyExistingValues('products', 'name', 'name_translations', $locale);
        $this->copyExistingValues('products', 'description', 'description_translations', $locale);
        $this->copyExistingValues('vendors', 'name', 'name_translations', $locale);
        $this->copyExistingValues('vendors', 'description', 'description_translations', $locale);
        $this->copyExistingValues('warehouses', 'name', 'name_translations', $locale);
        $this->copyExistingValues('warehouses', 'description', 'description_translations', $locale);
        $this->copyExistingValues('coupons', 'name', 'name_translations', $locale);
        $this->copyExistingValues('coupons', 'description', 'description_translations', $locale);
        $this->copyExistingValues('product_images', 'alt', 'alt_translations', $locale);
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn('alt_translations');
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn(['name_translations', 'description_translations']);
        });

        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn(['name_translations', 'description_translations']);
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['name_translations', 'description_translations']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('name_translations');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('name_translations');
        });
    }

    protected function copyExistingValues(string $table, string $sourceColumn, string $targetColumn, string $locale): void
    {
        if (! Schema::hasColumn($table, $sourceColumn)) {
            return;
        }

        DB::table($table)
            ->select('id', $sourceColumn)
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($table, $sourceColumn, $targetColumn, $locale) {
                foreach ($rows as $row) {
                    $value = $row->{$sourceColumn};

                    if ($value === null) {
                        continue;
                    }

                    DB::table($table)
                        ->where('id', $row->id)
                        ->update([
                            $targetColumn => json_encode([$locale => $value], JSON_UNESCAPED_UNICODE),
                        ]);
                }
            });
    }
};
