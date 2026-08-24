<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_item_blocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('news_item_id')->constrained('news_items')->cascadeOnDelete();
            $table->string('type', 20);
            $table->longText('body')->nullable();
            $table->text('image_url')->nullable();
            $table->string('image_public_id')->nullable();
            $table->string('image_caption', 160)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['news_item_id', 'sort_order']);
        });

        DB::table('news_items')
            ->select(['id', 'body', 'created_at', 'updated_at'])
            ->orderBy('id')
            ->get()
            ->each(function (object $item): void {
                $body = trim((string) $item->body);

                if ($body === '') {
                    return;
                }

                DB::table('news_item_blocks')->insert([
                    'news_item_id' => $item->id,
                    'type' => 'text',
                    'body' => $body,
                    'sort_order' => 0,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_item_blocks');
    }
};
