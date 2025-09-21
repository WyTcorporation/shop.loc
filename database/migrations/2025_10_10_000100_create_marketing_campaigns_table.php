<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_campaigns', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->foreignId('template_id')->nullable()->constrained('campaign_templates')->nullOnDelete();
            $table->string('status')->default('draft');
            $table->json('settings')->nullable();
            $table->json('audience_filters')->nullable();
            $table->timestamp('scheduled_for')->nullable();
            $table->timestamp('last_dispatched_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->unsignedInteger('open_count')->default(0);
            $table->unsignedInteger('click_count')->default(0);
            $table->unsignedInteger('conversion_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_campaigns');
    }
};
