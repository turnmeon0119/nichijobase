<?php

namespace Tests\Feature;

use App\Models\NewsItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_only_published_news_items(): void
    {
        NewsItem::query()->create([
            'title' => '公開News',
            'slug' => 'public-news',
            'body' => '公開本文',
            'is_public' => true,
            'published_at' => now()->subMinute(),
        ]);

        NewsItem::query()->create([
            'title' => '下書きNews',
            'slug' => 'draft-news',
            'body' => '下書き本文',
            'is_public' => false,
            'published_at' => now()->subMinute(),
        ]);

        $response = $this->getJson('/api/news');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'public-news')
            ->assertJsonMissing(['slug' => 'draft-news']);
    }

    public function test_it_returns_published_news_detail(): void
    {
        NewsItem::query()->create([
            'title' => '詳細News',
            'slug' => 'detail-news',
            'body' => '詳細本文',
            'is_public' => true,
            'published_at' => now()->subMinute(),
        ]);

        $response = $this->getJson('/api/news/detail-news');

        $response
            ->assertOk()
            ->assertJsonPath('data.title', '詳細News')
            ->assertJsonPath('data.body', '詳細本文');
    }

    public function test_it_hides_unpublished_news_detail(): void
    {
        NewsItem::query()->create([
            'title' => '非公開News',
            'slug' => 'private-news',
            'body' => '非公開本文',
            'is_public' => false,
            'published_at' => now()->subMinute(),
        ]);

        $this->getJson('/api/news/private-news')->assertNotFound();
    }
}
