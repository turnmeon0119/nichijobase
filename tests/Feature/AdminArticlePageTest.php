<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminArticlePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_redirects_admin_page_without_session_token(): void
    {
        $this->get('/admin')
            ->assertRedirect(route('admin.articles.login'));
    }

    public function test_it_logs_in_to_admin_articles_page(): void
    {
        $response = $this->post('/admin/articles/login', [
            'token' => config('app.admin_api_token'),
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('admin_web_token', config('app.admin_api_token'));
    }

    public function test_it_renders_admin_dashboard(): void
    {
        $this->withSession(['admin_web_token' => config('app.admin_api_token')])
            ->get('/admin')
            ->assertOk()
            ->assertSee('管理トップ')
            ->assertSee('記事管理')
            ->assertSee('掲示板管理')
            ->assertSee('大喜利管理');
    }

    public function test_it_lists_articles_with_ids_for_admin(): void
    {
        $article = Article::query()->create([
            'title' => '管理記事',
            'slug' => 'admin-article',
            'body' => '本文',
            'type' => 'episode',
            'published_at' => now(),
            'is_public' => true,
        ]);

        $this->withSession(['admin_web_token' => config('app.admin_api_token')])
            ->get('/admin/articles')
            ->assertOk()
            ->assertSee('管理記事')
            ->assertSee('#'.$article->id);
    }

    public function test_admin_can_move_article_to_trash(): void
    {
        $article = Article::query()->create([
            'title' => '削除対象記事',
            'slug' => 'delete-target',
            'body' => '本文',
            'type' => 'episode',
            'published_at' => now(),
            'is_public' => true,
        ]);

        $response = $this->withSession(['admin_web_token' => config('app.admin_api_token')])
            ->delete('/admin/articles/'.$article->id);

        $response->assertRedirect(route('admin.articles.index'));
        $this->assertSoftDeleted('articles', [
            'id' => $article->id,
        ]);
    }

    public function test_admin_can_restore_article_from_trash(): void
    {
        $article = Article::query()->create([
            'title' => '復元対象記事',
            'slug' => 'restore-target',
            'body' => '本文',
            'is_public' => false,
        ]);
        $article->delete();

        $response = $this->withSession(['admin_web_token' => config('app.admin_api_token')])
            ->patch('/admin/articles/'.$article->id.'/restore');

        $response->assertRedirect(route('admin.articles.trash'));
        $this->assertNotSoftDeleted('articles', [
            'id' => $article->id,
        ]);
    }

    public function test_admin_can_permanently_delete_article_from_trash(): void
    {
        $article = Article::query()->create([
            'title' => '完全削除対象記事',
            'slug' => 'force-delete-target',
            'body' => '本文',
            'is_public' => false,
        ]);
        $article->delete();

        $response = $this->withSession(['admin_web_token' => config('app.admin_api_token')])
            ->delete('/admin/articles/'.$article->id.'/force');

        $response->assertRedirect(route('admin.articles.trash'));
        $this->assertDatabaseMissing('articles', [
            'id' => $article->id,
        ]);
    }

    public function test_admin_can_create_draft_article(): void
    {
        $response = $this->withSession(['admin_web_token' => config('app.admin_api_token')])
            ->post('/admin/articles', [
                'title' => '下書き記事',
                'slug' => 'draft-article',
                'excerpt' => '概要',
                'body' => '本文',
                'type' => 'editorial',
                'published_at' => null,
                'is_public' => '0',
            ]);

        $article = Article::query()->where('slug', 'draft-article')->firstOrFail();

        $response->assertRedirect(route('admin.articles.edit', $article));
        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'title' => '下書き記事',
            'is_public' => false,
        ]);
    }

    public function test_admin_can_edit_and_publish_article(): void
    {
        $article = Article::query()->create([
            'title' => '編集前',
            'slug' => 'before-edit',
            'body' => '編集前本文',
            'type' => 'episode',
            'published_at' => null,
            'is_public' => false,
        ]);

        $response = $this->withSession(['admin_web_token' => config('app.admin_api_token')])
            ->put('/admin/articles/'.$article->id, [
                'title' => '編集後',
                'slug' => 'after-edit',
                'excerpt' => '更新概要',
                'body' => '編集後本文',
                'type' => 'editorial',
                'published_at' => now()->subMinute()->format('Y-m-d H:i:s'),
                'is_public' => '1',
            ]);

        $response->assertRedirect(route('admin.articles.edit', $article));
        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'title' => '編集後',
            'slug' => 'after-edit',
            'is_public' => true,
        ]);
    }

    public function test_admin_article_form_validates_duplicate_slug(): void
    {
        Article::query()->create([
            'title' => '既存記事',
            'slug' => 'existing-slug',
            'body' => '本文',
            'is_public' => false,
        ]);

        $response = $this->withSession(['admin_web_token' => config('app.admin_api_token')])
            ->from('/admin/articles/create')
            ->post('/admin/articles', [
                'title' => '重複記事',
                'slug' => 'existing-slug',
                'body' => '本文',
                'is_public' => '0',
            ]);

        $response->assertRedirect('/admin/articles/create')
            ->assertSessionHasErrors('slug');
    }
}
