<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\BoardThread;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoardApiTest extends TestCase
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

    public function test_it_creates_thread_without_auth(): void
    {
        $response = $this->postJson('/api/threads', [
            'title' => '雑談スレ',
            'name' => '名無し',
            'body' => 'こんにちは',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', '雑談スレ');

        $this->assertDatabaseHas('board_threads', [
            'title' => '雑談スレ',
            'body' => 'こんにちは',
        ]);
    }

    public function test_it_lists_threads(): void
    {
        BoardThread::query()->create([
            'title' => 'テストスレ',
            'body' => '本文',
        ]);

        $response = $this->getJson('/api/threads');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'article_id', 'title', 'name', 'body', 'created_at', 'reports_count', 'article', 'posts_count', 'latest_post_at'],
                ],
            ]);
    }

    public function test_it_sorts_threads_by_popular_reactions(): void
    {
        $popular = BoardThread::query()->create([
            'title' => '人気スレ',
            'body' => '本文',
            'empathy_count' => 3,
            'perspective_count' => 2,
        ]);

        $latest = BoardThread::query()->create([
            'title' => '新着スレ',
            'body' => '本文',
        ]);

        $this->getJson('/api/threads?sort=popular')
            ->assertOk()
            ->assertJsonPath('data.0.id', $popular->id)
            ->assertJsonPath('data.1.id', $latest->id);
    }

    public function test_it_rejects_invalid_thread_sort(): void
    {
        $this->getJson('/api/threads?sort=invalid')
            ->assertUnprocessable();
    }

    public function test_it_posts_reply_without_auth(): void
    {
        $thread = BoardThread::query()->create([
            'title' => 'テストスレ',
            'body' => '本文',
        ]);

        $response = $this->postJson('/api/threads/'.$thread->id.'/posts', [
            'name' => '',
            'body' => '返信テスト',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.board_thread_id', $thread->id);

        $this->assertDatabaseHas('board_posts', [
            'board_thread_id' => $thread->id,
            'body' => '返信テスト',
        ]);
    }

    public function test_it_shows_thread_with_posts(): void
    {
        $thread = BoardThread::query()->create([
            'title' => 'テストスレ',
            'body' => '本文',
        ]);

        $thread->posts()->create([
            'body' => '1件目',
        ]);

        $response = $this->getJson('/api/threads/'.$thread->id);

        $response->assertOk()
            ->assertJsonPath('data.title', 'テストスレ')
            ->assertJsonCount(1, 'data.posts');
    }

    public function test_it_creates_thread_linked_to_article(): void
    {
        $article = Article::query()->create([
            'title' => '連携記事',
            'slug' => 'linked-article',
            'body' => '本文',
            'type' => 'episode',
            'published_at' => now()->subDay(),
            'is_public' => true,
        ]);

        $response = $this->postJson('/api/threads', [
            'article_id' => $article->id,
            'title' => '記事連携スレ',
            'body' => '本文',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.article_id', $article->id)
            ->assertJsonPath('data.article.slug', 'linked-article');

        $this->assertDatabaseHas('board_threads', [
            'article_id' => $article->id,
            'title' => '記事連携スレ',
        ]);
    }

    public function test_it_finds_thread_by_article_slug(): void
    {
        $article = Article::query()->create([
            'title' => '連携記事',
            'slug' => 'linked-article',
            'body' => '本文',
            'type' => 'episode',
            'published_at' => now()->subDay(),
            'is_public' => true,
        ]);

        $thread = BoardThread::query()->create([
            'article_id' => $article->id,
            'title' => '記事連携スレ',
            'body' => '本文',
        ]);

        $response = $this->getJson('/api/articles/linked-article/thread');

        $response->assertOk()
            ->assertJsonPath('data.id', $thread->id)
            ->assertJsonPath('data.article_id', $article->id)
            ->assertJsonPath('data.article.slug', 'linked-article');
    }

    public function test_it_requires_admin_token_to_delete_thread(): void
    {
        $thread = BoardThread::query()->create([
            'title' => '削除対象スレ',
            'body' => '本文',
        ]);

        $response = $this->deleteJson('/api/threads/'.$thread->id);

        $response->assertUnauthorized();
        $this->assertDatabaseHas('board_threads', [
            'id' => $thread->id,
        ]);
    }

    public function test_admin_can_delete_thread(): void
    {
        $thread = BoardThread::query()->create([
            'title' => '削除対象スレ',
            'body' => '本文',
        ]);

        $response = $this->withHeaders($this->adminHeaders())
            ->deleteJson('/api/threads/'.$thread->id);

        $response->assertNoContent();
        $this->assertDatabaseMissing('board_threads', [
            'id' => $thread->id,
        ]);
    }

    public function test_admin_can_delete_post_in_thread(): void
    {
        $thread = BoardThread::query()->create([
            'title' => 'テストスレ',
            'body' => '本文',
        ]);

        $post = $thread->posts()->create([
            'body' => '削除対象返信',
        ]);

        $response = $this->withHeaders($this->adminHeaders())
            ->deleteJson('/api/threads/'.$thread->id.'/posts/'.$post->id);

        $response->assertNoContent();
        $this->assertDatabaseMissing('board_posts', [
            'id' => $post->id,
        ]);
    }

    public function test_it_reports_thread_without_auth(): void
    {
        $thread = BoardThread::query()->create([
            'title' => '通報対象',
            'body' => '本文',
        ]);

        $response = $this->postJson('/api/threads/'.$thread->id.'/report');

        $response->assertOk()
            ->assertJsonPath('data.id', $thread->id);

        $this->assertDatabaseHas('board_threads', [
            'id' => $thread->id,
            'reports_count' => 1,
        ]);
    }

    public function test_it_reacts_to_thread_without_auth(): void
    {
        $thread = BoardThread::query()->create([
            'title' => 'リアクション対象',
            'body' => '本文',
        ]);

        $this->postJson('/api/threads/'.$thread->id.'/reactions', ['type' => 'empathy'])
            ->assertOk()
            ->assertJsonPath('data.empathy_count', 1)
            ->assertJsonPath('data.perspective_count', 0);

        $this->postJson('/api/threads/'.$thread->id.'/reactions', ['type' => 'perspective'])
            ->assertOk()
            ->assertJsonPath('data.empathy_count', 1)
            ->assertJsonPath('data.perspective_count', 1);
    }

    public function test_it_rejects_invalid_reaction_type(): void
    {
        $thread = BoardThread::query()->create([
            'title' => 'リアクション対象',
            'body' => '本文',
        ]);

        $this->postJson('/api/threads/'.$thread->id.'/reactions', ['type' => 'bad'])
            ->assertUnprocessable();
    }

    public function test_it_reports_post_without_auth(): void
    {
        $thread = BoardThread::query()->create([
            'title' => 'スレ',
            'body' => '本文',
        ]);

        $post = $thread->posts()->create([
            'body' => '通報対象返信',
        ]);

        $response = $this->postJson('/api/threads/'.$thread->id.'/posts/'.$post->id.'/report');

        $response->assertOk()
            ->assertJsonPath('data.id', $post->id);

        $this->assertDatabaseHas('board_posts', [
            'id' => $post->id,
            'reports_count' => 1,
        ]);
    }

    public function test_admin_can_hide_and_unhide_thread(): void
    {
        $thread = BoardThread::query()->create([
            'title' => '対象スレ',
            'body' => '本文',
        ]);

        $hide = $this->withHeaders($this->adminHeaders())
            ->patchJson('/api/threads/'.$thread->id.'/hide');
        $hide->assertOk()
            ->assertJsonPath('data.is_hidden', true);

        $this->getJson('/api/threads/'.$thread->id)->assertNotFound();

        $unhide = $this->withHeaders($this->adminHeaders())
            ->patchJson('/api/threads/'.$thread->id.'/unhide');
        $unhide->assertOk()
            ->assertJsonPath('data.is_hidden', false);

        $this->getJson('/api/threads/'.$thread->id)->assertOk();
    }

    public function test_admin_can_hide_and_unhide_post(): void
    {
        $thread = BoardThread::query()->create([
            'title' => '対象スレ',
            'body' => '本文',
        ]);

        $post = $thread->posts()->create([
            'body' => '対象返信',
        ]);

        $this->withHeaders($this->adminHeaders())
            ->patchJson('/api/threads/'.$thread->id.'/posts/'.$post->id.'/hide')
            ->assertOk()
            ->assertJsonPath('data.is_hidden', true);

        $this->getJson('/api/threads/'.$thread->id)
            ->assertOk()
            ->assertJsonCount(0, 'data.posts');

        $this->withHeaders($this->adminHeaders())
            ->patchJson('/api/threads/'.$thread->id.'/posts/'.$post->id.'/unhide')
            ->assertOk()
            ->assertJsonPath('data.is_hidden', false);

        $this->getJson('/api/threads/'.$thread->id)
            ->assertOk()
            ->assertJsonCount(1, 'data.posts');
    }
}
