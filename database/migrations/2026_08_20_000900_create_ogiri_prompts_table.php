<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ogiri_prompts', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 160);
            $table->text('body')->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['is_public', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ogiri_prompts');
    }
};
