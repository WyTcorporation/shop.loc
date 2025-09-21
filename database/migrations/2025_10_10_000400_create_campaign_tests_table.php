<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_tests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_id')->constrained('marketing_campaigns')->cascadeOnDelete();
            $table->string('name');
            $table->foreignId('variant_a_template_id')->constrained('campaign_templates')->cascadeOnDelete();
            $table->foreignId('variant_b_template_id')->constrained('campaign_templates')->cascadeOnDelete();
            $table->unsignedInteger('traffic_split_a')->default(50);
            $table->unsignedInteger('traffic_split_b')->default(50);
            $table->string('status')->default('draft');
            $table->json('metrics')->nullable();
            $table->foreignId('winning_template_id')->nullable()->constrained('campaign_templates')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_tests');
    }
};
