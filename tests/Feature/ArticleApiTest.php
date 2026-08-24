<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\CloudinaryImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

    public function test_it_creates_article_with_image(): void
    {
        $this->mock(CloudinaryImageService::class, function ($mock): void {
            $mock->shouldReceive('upload')
                ->once()
                ->andReturn([
                    'image_url' => 'https://res.cloudinary.com/demo/image/upload/article.jpg',
                    'image_public_id' => 'nichijobase/articles/article',
                ]);
        });

        $response = $this->withHeaders($this->adminHeaders())
            ->post('/api/articles', [
                'title' => '画像つき記事',
                'slug' => 'article-with-image',
                'body' => '本文テキスト',
                'type' => 'episode',
                'published_at' => '2026-04-16 12:00:00',
                'is_public' => true,
                'image' => UploadedFile::fake()->create('article.jpg', 10, 'image/jpeg'),
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.image_url', 'https://res.cloudinary.com/demo/image/upload/article.jpg');

        $this->assertDatabaseHas('articles', [
            'slug' => 'article-with-image',
            'image_url' => 'https://res.cloudinary.com/demo/image/upload/article.jpg',
            'image_public_id' => 'nichijobase/articles/article',
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
        $this->assertSoftDeleted('articles', [
            'id' => $article->id,
        ]);
        $this->getJson('/api/articles/'.$article->slug)->assertNotFound();
    }

    public function test_it_deletes_article_by_id(): void
    {
        $this->seed();
        $article = Article::query()->firstOrFail();

        $response = $this->withHeaders($this->adminHeaders())->deleteJson('/api/articles/id/'.$article->id);

        $response->assertNoContent();
        $this->assertSoftDeleted('articles', [
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
                    '*' => ['id', 'title', 'slug', 'excerpt', 'image_url', 'type', 'published_at', 'board_thread_id'],
                ],
            ]);
    }

    public function test_it_filters_published_articles_by_keyword(): void
    {
        Article::query()->create([
            'title' => 'Docker環境のメモ',
            'slug' => 'docker-note',
            'excerpt' => 'ローカル開発の補足',
            'body' => 'Laravel API と Next.js をつなぐ話です。',
            'type' => 'episode',
            'published_at' => now()->subMinutes(10),
            'is_public' => true,
        ]);

        Article::query()->create([
            'title' => '雑談の記録',
            'slug' => 'random-note',
            'excerpt' => '別の話題',
            'body' => '検索対象ではない本文です。',
            'type' => 'editorial',
            'published_at' => now()->subMinutes(5),
            'is_public' => true,
        ]);

        Article::query()->create([
            'title' => '非公開のDockerメモ',
            'slug' => 'private-docker-note',
            'excerpt' => 'Docker',
            'body' => 'Docker',
            'type' => 'episode',
            'published_at' => now()->subMinutes(1),
            'is_public' => false,
        ]);

        $response = $this->getJson('/api/articles?q=Docker');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'docker-note')
            ->assertJsonMissing(['slug' => 'random-note'])
            ->assertJsonMissing(['slug' => 'private-docker-note']);
    }

    public function test_it_returns_article_detail_and_records_page_view(): void
    {
        $this->seed();
        $article = Article::query()->firstOrFail();

        $response = $this->getJson('/api/articles/'.$article->slug);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'title', 'slug', 'body', 'excerpt', 'image_url', 'type', 'published_at', 'view_count', 'board_thread_id'],
            ]);

        $this->assertDatabaseCount('page_views', 1);
        $this->assertDatabaseHas('page_views', [
            'article_id' => $article->id,
        ]);
    }
}
