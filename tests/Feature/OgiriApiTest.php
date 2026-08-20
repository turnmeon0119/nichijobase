<?php

namespace Tests\Feature;

use App\Models\OgiriPrompt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OgiriApiTest extends TestCase
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

    public function test_it_lists_published_prompts(): void
    {
        OgiriPrompt::query()->create([
            'title' => '公開お題',
            'body' => '本文',
            'is_public' => true,
            'published_at' => now()->subMinute(),
        ]);

        OgiriPrompt::query()->create([
            'title' => '非公開お題',
            'body' => '本文',
            'is_public' => false,
            'published_at' => now()->subMinute(),
        ]);

        $this->getJson('/api/ogiri/prompts')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', '公開お題');
    }

    public function test_admin_can_create_prompt(): void
    {
        $this->withHeaders($this->adminHeaders())
            ->postJson('/api/ogiri/prompts', [
                'title' => '新しいお題',
                'body' => '回答してください',
                'is_public' => true,
                'published_at' => now()->toDateTimeString(),
            ])
            ->assertCreated()
            ->assertJsonPath('data.title', '新しいお題');

        $this->assertDatabaseHas('ogiri_prompts', [
            'title' => '新しいお題',
        ]);
    }

    public function test_it_requires_admin_token_to_create_prompt(): void
    {
        $this->postJson('/api/ogiri/prompts', [
            'title' => '新しいお題',
        ])->assertUnauthorized();
    }

    public function test_it_posts_answer_without_auth(): void
    {
        $prompt = OgiriPrompt::query()->create([
            'title' => '回答対象',
            'body' => '本文',
            'is_public' => true,
            'published_at' => now()->subMinute(),
        ]);

        $this->postJson('/api/ogiri/prompts/'.$prompt->id.'/answers', [
            'name' => '',
            'body' => 'これは回答です',
        ])
            ->assertCreated()
            ->assertJsonPath('data.body', 'これは回答です');

        $this->assertDatabaseHas('ogiri_answers', [
            'ogiri_prompt_id' => $prompt->id,
            'body' => 'これは回答です',
        ]);
    }

    public function test_it_reacts_to_answer_without_auth(): void
    {
        $prompt = OgiriPrompt::query()->create([
            'title' => '回答対象',
            'body' => '本文',
            'is_public' => true,
            'published_at' => now()->subMinute(),
        ]);

        $answer = $prompt->answers()->create([
            'body' => '回答',
        ]);

        $this->postJson('/api/ogiri/prompts/'.$prompt->id.'/answers/'.$answer->id.'/reactions', ['type' => 'funny'])
            ->assertOk()
            ->assertJsonPath('data.funny_count', 1)
            ->assertJsonPath('data.genius_count', 0);

        $this->postJson('/api/ogiri/prompts/'.$prompt->id.'/answers/'.$answer->id.'/reactions', ['type' => 'genius'])
            ->assertOk()
            ->assertJsonPath('data.funny_count', 1)
            ->assertJsonPath('data.genius_count', 1);
    }
}
