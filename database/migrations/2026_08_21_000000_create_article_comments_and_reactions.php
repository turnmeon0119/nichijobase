<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->unsignedInteger('like_count')->default(0)->after('is_public');
            $table->unsignedInteger('empathy_count')->default(0)->after('like_count');
            $table->unsignedInteger('useful_count')->default(0)->after('empathy_count');
        });

        Schema::create('article_comments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->string('name', 40)->nullable();
            $table->text('body');
            $table->string('created_ip', 45)->nullable();
            $table->timestamps();

            $table->index(['article_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_comments');

        Schema::table('articles', function (Blueprint $table): void {
            $table->dropColumn(['like_count', 'empathy_count', 'useful_count']);
        });
    }
};
