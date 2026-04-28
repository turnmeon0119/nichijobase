<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\BoardThread;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoardPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_board_index_page(): void
    {
        $thread = BoardThread::query()->create([
            'title' => '公開スレ',
            'body' => '本文',
        ]);

        $response = $this->get('/board');

        $response->assertOk()
            ->assertSee('日常BASE')
            ->assertSee('公開スレ')
            ->assertSee(route('board.show', $thread), false);
    }

    public function test_it_renders_timeline_page(): void
    {
        $thread = BoardThread::query()->create([
            'title' => 'タイムライン対象',
            'body' => '最初の投稿',
        ]);

        $thread->posts()->create([
            'body' => '返信も流れる',
        ]);

        $response = $this->get('/timeline');

        $response->assertOk()
            ->assertSee('タイムライン')
            ->assertSee('タイムライン対象')
            ->assertSee('返信も流れる');
    }

    public function test_it_renders_board_detail_page(): void
    {
        $thread = BoardThread::query()->create([
            'title' => '詳細スレ',
            'body' => 'スレ本文',
        ]);

        $thread->posts()->create([
            'body' => '返信本文',
        ]);

        $response = $this->get('/board/'.$thread->id);

        $response->assertOk()
            ->assertSee('詳細スレ')
            ->assertSee('返信本文');
    }

    public function test_it_creates_thread_from_board_page(): void
    {
        $article = Article::query()->create([
            'title' => '記事',
            'slug' => 'kiji',
            'body' => '本文',
            'type' => 'episode',
            'published_at' => now()->subDay(),
            'is_public' => true,
        ]);

        $response = $this->post('/board', [
            'article_id' => $article->id,
            'title' => '画面作成スレ',
            'name' => '投稿者',
            'body' => '画面から作成',
        ]);

        $thread = BoardThread::query()->where('title', '画面作成スレ')->firstOrFail();

        $response->assertRedirect(route('board.show', $thread));
        $this->assertDatabaseHas('board_threads', [
            'id' => $thread->id,
            'article_id' => $article->id,
            'name' => '投稿者',
        ]);
    }

    public function test_it_posts_reply_from_board_page(): void
    {
        $thread = BoardThread::query()->create([
            'title' => '返信対象',
            'body' => '本文',
        ]);

        $response = $this->post('/board/'.$thread->id.'/posts', [
            'name' => '返信者',
            'body' => '画面から返信',
        ]);

        $response->assertRedirect(route('board.show', $thread));
        $this->assertDatabaseHas('board_posts', [
            'board_thread_id' => $thread->id,
            'name' => '返信者',
            'body' => '画面から返信',
        ]);
    }
}
