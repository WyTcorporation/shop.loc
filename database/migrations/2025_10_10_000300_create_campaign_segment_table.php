<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_segment', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_id')->constrained('marketing_campaigns')->cascadeOnDelete();
            $table->foreignId('segment_id')->constrained('customer_segments')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['campaign_id', 'segment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_segment');
    }
};
