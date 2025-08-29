<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        DB::table('orders')->whereNull('total')->update(['total' => 0]);
        $driver = DB::connection()->getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE orders ALTER COLUMN total SET DEFAULT 0;');
        } elseif (in_array($driver, ['mysql', 'mariadb'])) {
            DB::statement('ALTER TABLE orders MODIFY total DECIMAL(12,2) NOT NULL DEFAULT 0;');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE orders ALTER COLUMN total DROP DEFAULT;');
        } elseif (in_array($driver, ['mysql', 'mariadb'])) {
            DB::statement('ALTER TABLE orders MODIFY total DECIMAL(12,2) NOT NULL;');
        }
    }
};
