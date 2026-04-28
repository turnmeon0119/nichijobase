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
            ->assertSee('記事を管理する')
            ->assertSee('掲示板を管理モードで開く');
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

    public function test_admin_can_delete_article_from_admin_page(): void
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
        $this->assertDatabaseMissing('articles', [
            'id' => $article->id,
        ]);
    }
}
