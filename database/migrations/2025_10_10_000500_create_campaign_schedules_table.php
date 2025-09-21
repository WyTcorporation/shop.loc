<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_id')->constrained('marketing_campaigns')->cascadeOnDelete();
            $table->string('cron_expression');
            $table->string('timezone')->default('UTC');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_schedules');
    }
};
