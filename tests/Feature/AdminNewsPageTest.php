<?php

namespace Tests\Feature;

use App\Models\NewsItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNewsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_news_link_on_admin_dashboard(): void
    {
        $this->withSession(['admin_web_token' => config('app.admin_api_token')])
            ->get('/admin')
            ->assertOk()
            ->assertSee('News管理');
    }

    public function test_it_lists_news_items_for_admin(): void
    {
        $item = NewsItem::query()->create([
            'title' => '管理News',
            'slug' => 'admin-news',
            'body' => '本文',
            'is_public' => true,
            'published_at' => now(),
        ]);

        $this->withSession(['admin_web_token' => config('app.admin_api_token')])
            ->get('/admin/news')
            ->assertOk()
            ->assertSee('管理News')
            ->assertSee('#'.$item->id);
    }

    public function test_admin_can_create_news_item(): void
    {
        $response = $this->withSession(['admin_web_token' => config('app.admin_api_token')])
            ->post('/admin/news', [
                'title' => '新しいNews',
                'slug' => 'new-news',
                'body' => 'News本文',
                'published_at' => now()->format('Y-m-d H:i:s'),
                'is_public' => '1',
            ]);

        $item = NewsItem::query()->where('slug', 'new-news')->firstOrFail();

        $response->assertRedirect(route('admin.news.edit', $item));
        $this->assertDatabaseHas('news_items', [
            'id' => $item->id,
            'title' => '新しいNews',
            'is_public' => true,
        ]);
    }

    public function test_admin_can_edit_news_item(): void
    {
        $item = NewsItem::query()->create([
            'title' => '編集前News',
            'slug' => 'before-news',
            'body' => '編集前本文',
            'is_public' => false,
            'published_at' => null,
        ]);

        $response = $this->withSession(['admin_web_token' => config('app.admin_api_token')])
            ->put('/admin/news/'.$item->id, [
                'title' => '編集後News',
                'slug' => 'after-news',
                'body' => '編集後本文',
                'published_at' => now()->format('Y-m-d H:i:s'),
                'is_public' => '1',
            ]);

        $response->assertRedirect(route('admin.news.edit', $item));
        $this->assertDatabaseHas('news_items', [
            'id' => $item->id,
            'title' => '編集後News',
            'slug' => 'after-news',
            'is_public' => true,
        ]);
    }

    public function test_admin_can_delete_news_item(): void
    {
        $item = NewsItem::query()->create([
            'title' => '削除News',
            'slug' => 'delete-news',
            'body' => '本文',
            'is_public' => true,
            'published_at' => now(),
        ]);

        $response = $this->withSession(['admin_web_token' => config('app.admin_api_token')])
            ->delete('/admin/news/'.$item->id);

        $response->assertRedirect(route('admin.news.index'));
        $this->assertDatabaseMissing('news_items', [
            'id' => $item->id,
        ]);
    }
}
