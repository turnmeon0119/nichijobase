<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function adminHeaders(): array
    {
        return [
            'X-Admin-Token' => (string) config('app.admin_api_token'),
        ];
    }

    public function test_it_creates_article(): void
    {
        $payload = [
            'title' => '新規記事タイトル',
            'slug' => 'new-article-title',
            'excerpt' => '概要テキスト',
            'body' => '本文テキスト',
            'type' => 'episode',
            'published_at' => '2026-04-16 12:00:00',
            'is_public' => true,
        ];

        $response = $this->withHeaders($this->adminHeaders())
            ->postJson('/api/articles', $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('data.slug', 'new-article-title');

        $this->assertDatabaseHas('articles', [
            'slug' => 'new-article-title',
            'title' => '新規記事タイトル',
        ]);
    }

    public function test_it_rejects_invalid_article_payload(): void
    {
        $response = $this->withHeaders($this->adminHeaders())->postJson('/api/articles', [
            'title' => '',
            'slug' => 'invalid slug',
            'body' => '',
            'type' => 'other',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'slug', 'body', 'type']);
    }

    public function test_it_requires_admin_token_for_writing(): void
    {
        $response = $this->postJson('/api/articles', [
            'title' => 'No Token',
            'slug' => 'no-token',
            'body' => 'forbidden',
        ]);

        $response->assertUnauthorized();
    }

    public function test_it_updates_article_by_slug(): void
    {
        $this->seed();
        $article = Article::query()->firstOrFail();

        $response = $this->withHeaders($this->adminHeaders())->putJson('/api/articles/'.$article->slug, [
            'title' => '更新後タイトル',
            'slug' => 'updated-slug',
            'type' => 'editorial',
            'is_public' => false,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.slug', 'updated-slug')
            ->assertJsonPath('data.title', '更新後タイトル')
            ->assertJsonPath('data.type', 'editorial')
            ->assertJsonPath('data.is_public', false);

        $this->assertDatabaseHas('articles', [
            'slug' => 'updated-slug',
            'title' => '更新後タイトル',
            'is_public' => false,
        ]);
    }

    public function test_it_deletes_article_by_slug(): void
    {
        $this->seed();
        $article = Article::query()->firstOrFail();

        $response = $this->withHeaders($this->adminHeaders())->deleteJson('/api/articles/'.$article->slug);

        $response->assertNoContent();
        $this->assertDatabaseMissing('articles', [
            'id' => $article->id,
        ]);
    }

    public function test_it_lists_published_articles(): void
    {
        $this->seed();

        $response = $this->getJson('/api/articles');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'slug', 'excerpt', 'type', 'published_at', 'board_thread_id'],
                ],
            ]);
    }

    public function test_it_returns_article_detail_and_records_page_view(): void
    {
        $this->seed();
        $article = Article::query()->firstOrFail();

        $response = $this->getJson('/api/articles/'.$article->slug);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'title', 'slug', 'body', 'excerpt', 'type', 'published_at', 'view_count', 'board_thread_id'],
            ]);

        $this->assertDatabaseCount('page_views', 1);
        $this->assertDatabaseHas('page_views', [
            'article_id' => $article->id,
        ]);
    }
}
