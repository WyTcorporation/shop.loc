<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $defaultDisk = config('shop.product_images_disk', 'public');

        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            Schema::table('product_images', function (Blueprint $table) use ($defaultDisk) {
                $table->string('disk_tmp')->default($defaultDisk);
            });

            DB::table('product_images')->update([
                'disk_tmp' => DB::raw("CASE WHEN disk IS NULL OR disk = '' THEN '{$defaultDisk}' ELSE disk END"),
            ]);

            Schema::table('product_images', function (Blueprint $table) {
                $table->dropColumn('disk');
            });

            Schema::table('product_images', function (Blueprint $table) {
                $table->renameColumn('disk_tmp', 'disk');
            });
        } else {
            match ($driver) {
                'pgsql' => DB::statement("ALTER TABLE product_images ALTER COLUMN disk SET DEFAULT '{$defaultDisk}'"),
                'mysql' => DB::statement("ALTER TABLE product_images MODIFY disk VARCHAR(255) NOT NULL DEFAULT '{$defaultDisk}'"),
                'sqlsrv' => DB::unprepared(<<<SQL
DECLARE @constraintName NVARCHAR(200);
SELECT @constraintName = df.name
FROM sys.default_constraints df
    INNER JOIN sys.columns c ON df.parent_object_id = c.object_id AND df.parent_column_id = c.column_id
    INNER JOIN sys.tables t ON df.parent_object_id = t.object_id
WHERE t.name = 'product_images' AND c.name = 'disk';

IF @constraintName IS NOT NULL
    EXEC('ALTER TABLE product_images DROP CONSTRAINT ' + QUOTENAME(@constraintName));

ALTER TABLE product_images ADD CONSTRAINT DF_product_images_disk DEFAULT '{$defaultDisk}' FOR disk;
SQL),
                default => null,
            };
        }

        DB::table('product_images')
            ->where(function ($query) {
                $query->whereNull('disk')->orWhere('disk', '');
            })
            ->update(['disk' => $defaultDisk]);
        // Environments that need to move assets between disks should run a
        // dedicated operation (such as a one-off command) to copy files to the
        // new storage disk before updating the records.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $defaultDisk = 's3';

        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            Schema::table('product_images', function (Blueprint $table) use ($defaultDisk) {
                $table->string('disk_tmp')->default($defaultDisk);
            });

            DB::table('product_images')->update([
                'disk_tmp' => DB::raw("CASE WHEN disk IS NULL OR disk = '' THEN '{$defaultDisk}' ELSE disk END"),
            ]);

            Schema::table('product_images', function (Blueprint $table) {
                $table->dropColumn('disk');
            });

            Schema::table('product_images', function (Blueprint $table) {
                $table->renameColumn('disk_tmp', 'disk');
            });
        } else {
            match ($driver) {
                'pgsql' => DB::statement("ALTER TABLE product_images ALTER COLUMN disk SET DEFAULT '{$defaultDisk}'"),
                'mysql' => DB::statement("ALTER TABLE product_images MODIFY disk VARCHAR(255) NOT NULL DEFAULT '{$defaultDisk}'"),
                'sqlsrv' => DB::unprepared(<<<SQL
DECLARE @constraintName NVARCHAR(200);
SELECT @constraintName = df.name
FROM sys.default_constraints df
    INNER JOIN sys.columns c ON df.parent_object_id = c.object_id AND df.parent_column_id = c.column_id
    INNER JOIN sys.tables t ON df.parent_object_id = t.object_id
WHERE t.name = 'product_images' AND c.name = 'disk';

IF @constraintName IS NOT NULL
    EXEC('ALTER TABLE product_images DROP CONSTRAINT ' + QUOTENAME(@constraintName));

ALTER TABLE product_images ADD CONSTRAINT DF_product_images_disk DEFAULT 's3' FOR disk;
SQL),
                default => null,
            };
        }

        DB::table('product_images')
            ->where(function ($query) {
                $query->whereNull('disk')->orWhere('disk', '');
            })
            ->update(['disk' => $defaultDisk]);
    }
};
