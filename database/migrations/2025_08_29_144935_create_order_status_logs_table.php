<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_status_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->string('from_status')->nullable();
            $t->string('to_status');
            $t->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->text('note')->nullable();
            $t->timestamps();

            $t->index(['order_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_logs');
    }
};
