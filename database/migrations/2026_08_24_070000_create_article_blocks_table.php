<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_blocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->string('type', 20);
            $table->longText('body')->nullable();
            $table->text('image_url')->nullable();
            $table->string('image_public_id')->nullable();
            $table->string('image_caption', 160)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['article_id', 'sort_order']);
        });

        DB::table('articles')
            ->select(['id', 'body', 'created_at', 'updated_at'])
            ->orderBy('id')
            ->get()
            ->each(function (object $article): void {
                $body = trim((string) $article->body);

                if ($body === '') {
                    return;
                }

                DB::table('article_blocks')->insert([
                    'article_id' => $article->id,
                    'type' => 'text',
                    'body' => $body,
                    'sort_order' => 0,
                    'created_at' => $article->created_at,
                    'updated_at' => $article->updated_at,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_blocks');
    }
};
