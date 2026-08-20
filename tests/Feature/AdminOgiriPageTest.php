<?php

namespace Tests\Feature;

use App\Models\OgiriPrompt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOgiriPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_ogiri_prompts_for_admin(): void
    {
        $prompt = OgiriPrompt::query()->create([
            'title' => '画像で一言',
            'body' => '短くお願いします',
            'is_public' => true,
            'published_at' => now(),
        ]);

        $this->withSession(['admin_web_token' => config('app.admin_api_token')])
            ->get('/admin/ogiri')
            ->assertOk()
            ->assertSee('大喜利管理')
            ->assertSee('#'.$prompt->id)
            ->assertSee('画像で一言');
    }

    public function test_admin_can_create_ogiri_prompt(): void
    {
        $response = $this->withSession(['admin_web_token' => config('app.admin_api_token')])
            ->post('/admin/ogiri', [
                'title' => 'この写真に一言',
                'body' => '回答は280字まで',
                'is_public' => '1',
                'published_at' => now()->format('Y-m-d H:i:s'),
            ]);

        $response->assertRedirect(route('admin.ogiri.index'));
        $this->assertDatabaseHas('ogiri_prompts', [
            'title' => 'この写真に一言',
            'body' => '回答は280字まで',
            'is_public' => true,
        ]);
    }

    public function test_admin_can_delete_ogiri_prompt(): void
    {
        $prompt = OgiriPrompt::query()->create([
            'title' => '削除対象のお題',
            'is_public' => true,
            'published_at' => now(),
        ]);

        $response = $this->withSession(['admin_web_token' => config('app.admin_api_token')])
            ->delete('/admin/ogiri/'.$prompt->id);

        $response->assertRedirect(route('admin.ogiri.index'));
        $this->assertDatabaseMissing('ogiri_prompts', [
            'id' => $prompt->id,
        ]);
    }
}
