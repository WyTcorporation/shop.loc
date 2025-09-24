<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<string, array<int, string>>
     */
    protected array $translationColumns = [
        'categories' => ['name_translations'],
        'products' => ['name_translations', 'description_translations'],
        'vendors' => ['name_translations', 'description_translations'],
        'warehouses' => ['name_translations', 'description_translations'],
        'coupons' => ['name_translations', 'description_translations'],
        'product_images' => ['alt_translations'],
    ];

    public function up(): void
    {
        $this->convertColumnsTo('jsonb');
    }

    public function down(): void
    {
        $this->convertColumnsTo('json');
    }

    protected function convertColumnsTo(string $type): void
    {
        foreach ($this->translationColumns as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                DB::statement(sprintf(
                    'ALTER TABLE "%s" ALTER COLUMN "%s" TYPE %s USING "%s"::%s',
                    $table,
                    $column,
                    $type,
                    $column,
                    $type,
                ));
            }
        }
    }
};
