<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('channel');
            $table->string('subject')->nullable();
            $table->text('content')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_templates');
    }
};
