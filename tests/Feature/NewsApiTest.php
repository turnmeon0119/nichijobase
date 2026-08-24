<?php

namespace Tests\Feature;

use App\Models\NewsItem;
use App\Models\NewsItemBlock;
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
        $item = NewsItem::query()->create([
            'title' => '詳細News',
            'slug' => 'detail-news',
            'body' => '詳細本文',
            'is_public' => true,
            'published_at' => now()->subMinute(),
        ]);

        NewsItemBlock::query()->create([
            'news_item_id' => $item->id,
            'type' => 'text',
            'body' => 'ブロック本文',
            'sort_order' => 0,
        ]);

        NewsItemBlock::query()->create([
            'news_item_id' => $item->id,
            'type' => 'image',
            'image_url' => 'https://example.com/news.jpg',
            'image_caption' => 'News画像',
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/news/detail-news');

        $response
            ->assertOk()
            ->assertJsonPath('data.title', '詳細News')
            ->assertJsonPath('data.body', '詳細本文')
            ->assertJsonPath('data.blocks.0.type', 'text')
            ->assertJsonPath('data.blocks.0.body', 'ブロック本文')
            ->assertJsonPath('data.blocks.1.type', 'image')
            ->assertJsonPath('data.blocks.1.image_url', 'https://example.com/news.jpg')
            ->assertJsonPath('data.blocks.1.image_caption', 'News画像');
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

    public function test_it_filters_news_items_by_keyword(): void
    {
        NewsItem::query()->create([
            'title' => 'イベントのお知らせ',
            'slug' => 'event-news',
            'body' => '日常BASEのイベント情報です',
            'is_public' => true,
            'published_at' => now()->subMinute(),
        ]);

        NewsItem::query()->create([
            'title' => '別のお知らせ',
            'slug' => 'other-news',
            'body' => '通常のお知らせです',
            'is_public' => true,
            'published_at' => now()->subMinute(),
        ]);

        $response = $this->getJson('/api/news?q=イベント');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'event-news')
            ->assertJsonMissing(['slug' => 'other-news']);
    }

    public function test_it_rejects_too_long_news_search_keyword(): void
    {
        $keyword = str_repeat('a', 101);

        $this->getJson('/api/news?q='.$keyword)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('q');
    }
}
