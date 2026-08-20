<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ogiri_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ogiri_prompt_id')->constrained()->cascadeOnDelete();
            $table->string('name', 40)->nullable();
            $table->text('body');
            $table->string('created_ip', 45)->nullable();
            $table->boolean('is_hidden')->default(false);
            $table->unsignedInteger('reports_count')->default(0);
            $table->unsignedInteger('funny_count')->default(0);
            $table->unsignedInteger('genius_count')->default(0);
            $table->timestamps();

            $table->index(['ogiri_prompt_id', 'is_hidden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ogiri_answers');
    }
};
