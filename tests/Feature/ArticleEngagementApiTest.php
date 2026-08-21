<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleEngagementApiTest extends TestCase
{
    use RefreshDatabase;

    private function createPublishedArticle(): Article
    {
        return Article::query()->create([
            'title' => 'コメント対象記事',
            'slug' => 'comment-target',
            'body' => '本文',
            'type' => 'episode',
            'published_at' => now()->subMinute(),
            'is_public' => true,
        ]);
    }

    public function test_it_posts_article_comment_without_auth(): void
    {
        $article = $this->createPublishedArticle();

        $response = $this->postJson('/api/articles/'.$article->slug.'/comments', [
            'name' => '読者',
            'body' => '記事へのコメントです',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', '読者')
            ->assertJsonPath('data.body', '記事へのコメントです');

        $this->assertDatabaseHas('article_comments', [
            'article_id' => $article->id,
            'body' => '記事へのコメントです',
        ]);
    }

    public function test_it_lists_article_comments(): void
    {
        $article = $this->createPublishedArticle();
        $article->comments()->create([
            'body' => '1件目',
        ]);

        $this->getJson('/api/articles/'.$article->slug.'/comments')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.body', '1件目');
    }

    public function test_it_reacts_to_article_without_auth(): void
    {
        $article = $this->createPublishedArticle();

        $this->postJson('/api/articles/'.$article->slug.'/reactions', [
            'type' => 'empathy',
        ])
            ->assertOk()
            ->assertJsonPath('data.empathy_count', 1)
            ->assertJsonPath('data.like_count', 0)
            ->assertJsonPath('data.useful_count', 0);

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'empathy_count' => 1,
        ]);
    }

    public function test_article_detail_includes_engagement_counts(): void
    {
        $article = $this->createPublishedArticle();
        $article->comments()->create([
            'body' => 'コメントあり',
        ]);
        $article->update([
            'like_count' => 2,
            'empathy_count' => 3,
            'useful_count' => 4,
        ]);

        $this->getJson('/api/articles/'.$article->slug)
            ->assertOk()
            ->assertJsonPath('data.comments_count', 1)
            ->assertJsonPath('data.like_count', 2)
            ->assertJsonPath('data.empathy_count', 3)
            ->assertJsonPath('data.useful_count', 4);
    }
}
