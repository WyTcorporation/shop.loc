<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'description')) {
            Schema::table('products', function (Blueprint $table) {
                $table->text('description')->nullable()->after('name');
            });
        }

        if (! Schema::hasColumn('products', 'description_translations')) {
            Schema::table('products', function (Blueprint $table) {
                $table->json('description_translations')->nullable()->after('description');
            });
        }

        if (! Schema::hasColumn('products', 'description') || ! Schema::hasColumn('products', 'description_translations')) {
            return;
        }

        $locale = config('app.locale');

        DB::table('products')
            ->select(['id', 'description', 'description_translations'])
            ->whereNotNull('description')
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($locale) {
                foreach ($rows as $row) {
                    $translations = [];

                    if (! empty($row->description_translations)) {
                        $decoded = json_decode($row->description_translations, true);
                        if (is_array($decoded)) {
                            $translations = $decoded;
                        }
                    }

                    if (array_key_exists($locale, $translations) && $translations[$locale] !== null && $translations[$locale] !== '') {
                        continue;
                    }

                    $translations[$locale] = $row->description;

                    DB::table('products')
                        ->where('id', $row->id)
                        ->update([
                            'description_translations' => json_encode($translations, JSON_UNESCAPED_UNICODE),
                        ]);
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'description_translations')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('description_translations');
            });
        }

        if (Schema::hasColumn('products', 'description')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }
};
