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

        $response = $this->withSession(['admin_web_token' => config('app.admin_api_token')])
            ->get('/admin/board');

        $response->assertOk()
            ->assertSee('日常BASE')
            ->assertSee('公開スレ')
            ->assertSee(route('admin.board.show', $thread), false);
    }

    public function test_it_prefills_remembered_name_on_board_pages(): void
    {
        $thread = BoardThread::query()->create([
            'title' => '名前記憶スレ',
            'body' => '本文',
        ]);

        $this->withSession([
            'board_name' => '記憶済み',
            'admin_web_token' => config('app.admin_api_token'),
        ])
            ->get('/admin/board')
            ->assertSee('value="記憶済み"', false);

        $this->withSession([
            'board_name' => '記憶済み',
            'admin_web_token' => config('app.admin_api_token'),
        ])
            ->get('/admin/board/'.$thread->id)
            ->assertSee('value="記憶済み"', false);
    }

    public function test_it_shows_admin_controls_when_admin_session_exists(): void
    {
        $thread = BoardThread::query()->create([
            'title' => '管理スレ',
            'body' => '本文',
        ]);

        $this->withSession(['admin_web_token' => config('app.admin_api_token')])
            ->get('/admin/board')
            ->assertSee('管理モードで表示中です。')
            ->assertSee('管理用ID: #'.$thread->id)
            ->assertSee('このスレを削除')
            ->assertSee('管理モードを解除');
    }

    public function test_admin_can_delete_thread_from_board_ui(): void
    {
        $thread = BoardThread::query()->create([
            'title' => '削除対象スレ',
            'body' => '本文',
        ]);

        $response = $this->withSession(['admin_web_token' => config('app.admin_api_token')])
            ->delete('/admin/board/'.$thread->id);

        $response->assertRedirect(route('admin.board.index'));
        $this->assertDatabaseMissing('board_threads', [
            'id' => $thread->id,
        ]);
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

        $response = $this->withSession(['admin_web_token' => config('app.admin_api_token')])
            ->get('/admin/timeline');

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

        $response = $this->withSession(['admin_web_token' => config('app.admin_api_token')])
            ->get('/admin/board/'.$thread->id);

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

        $response = $this->withSession(['admin_web_token' => config('app.admin_api_token')])
            ->post('/admin/board', [
            'article_id' => $article->id,
            'title' => '画面作成スレ',
            'name' => '投稿者',
            'body' => '画面から作成',
        ]);

        $thread = BoardThread::query()->where('title', '画面作成スレ')->firstOrFail();

        $response->assertRedirect(route('admin.board.show', $thread).'#thread-'.$thread->id);
        $response->assertSessionHas('board_name', '投稿者');
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

        $response = $this->withSession(['admin_web_token' => config('app.admin_api_token')])
            ->post('/admin/board/'.$thread->id.'/posts', [
            'name' => '返信者',
            'body' => '画面から返信',
        ]);

        $postId = $thread->posts()->value('id');

        $response->assertRedirect(route('admin.board.show', $thread).'#post-'.$postId);
        $response->assertSessionHas('board_name', '返信者');
        $this->assertDatabaseHas('board_posts', [
            'board_thread_id' => $thread->id,
            'name' => '返信者',
            'body' => '画面から返信',
        ]);
    }

    public function test_admin_can_delete_reply_from_board_detail_page(): void
    {
        $thread = BoardThread::query()->create([
            'title' => '返信削除対象',
            'body' => '本文',
        ]);

        $post = $thread->posts()->create([
            'body' => '削除される返信',
        ]);

        $response = $this->withSession(['admin_web_token' => config('app.admin_api_token')])
            ->delete('/admin/board/'.$thread->id.'/posts/'.$post->id);

        $response->assertRedirect(route('admin.board.show', $thread).'#replies');
        $this->assertDatabaseMissing('board_posts', [
            'id' => $post->id,
        ]);
        $this->assertDatabaseHas('board_threads', [
            'id' => $thread->id,
        ]);
    }

    public function test_public_board_page_is_not_exposed_by_laravel(): void
    {
        $this->get('/board')->assertNotFound();
    }

    public function test_admin_board_requires_admin_session(): void
    {
        $this->get('/admin/board')
            ->assertRedirect(route('admin.articles.login'));
    }
}
