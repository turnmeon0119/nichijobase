<?php

namespace Tests\Feature;

use App\Models\HitokotoPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HitokotoApiTest extends TestCase
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

    public function test_it_creates_post_without_auth(): void
    {
        $response = $this->postJson('/api/hitokoto', [
            'name' => '名無し',
            'body' => 'きょうは晴れ',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.body', 'きょうは晴れ')
            ->assertJsonPath('data.name', '名無し')
            ->assertJsonPath('data.reports_count', 0);

        $this->assertDatabaseHas('hitokoto_posts', [
            'body' => 'きょうは晴れ',
        ]);
    }

    public function test_it_defaults_blank_name_to_null(): void
    {
        $response = $this->postJson('/api/hitokoto', [
            'name' => '',
            'body' => '名前なしの投稿',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', null);
    }

    public function test_it_rejects_body_over_140_characters(): void
    {
        $response = $this->postJson('/api/hitokoto', [
            'body' => str_repeat('あ', 141),
        ]);

        $response->assertUnprocessable();
    }

    public function test_it_rejects_missing_body(): void
    {
        $response = $this->postJson('/api/hitokoto', []);

        $response->assertUnprocessable();
    }

    public function test_it_lists_posts_newest_first(): void
    {
        $first = HitokotoPost::query()->create(['body' => '1件目']);
        $second = HitokotoPost::query()->create(['body' => '2件目']);

        $response = $this->getJson('/api/hitokoto');

        $response->assertOk()
            ->assertJsonPath('data.0.id', $second->id)
            ->assertJsonPath('data.1.id', $first->id)
            ->assertJsonStructure([
                'data' => ['*' => ['id', 'name', 'body', 'created_at', 'reports_count']],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_it_excludes_hidden_posts_from_list(): void
    {
        HitokotoPost::query()->create(['body' => '表示される', 'is_hidden' => false]);
        HitokotoPost::query()->create(['body' => '非表示', 'is_hidden' => true]);

        $this->getJson('/api/hitokoto')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_it_reports_post_without_auth(): void
    {
        $post = HitokotoPost::query()->create(['body' => '通報対象']);

        $response = $this->postJson('/api/hitokoto/'.$post->id.'/report');

        $response->assertOk()
            ->assertJsonPath('data.id', $post->id)
            ->assertJsonPath('data.reports_count', 1);

        $this->assertDatabaseHas('hitokoto_posts', [
            'id' => $post->id,
            'reports_count' => 1,
        ]);
    }

    public function test_it_hides_post_after_three_reports(): void
    {
        $post = HitokotoPost::query()->create(['body' => '自動非表示対象']);

        $this->postJson('/api/hitokoto/'.$post->id.'/report')
            ->assertOk()->assertJsonPath('data.is_hidden', false);
        $this->postJson('/api/hitokoto/'.$post->id.'/report')
            ->assertOk()->assertJsonPath('data.is_hidden', false);
        $this->postJson('/api/hitokoto/'.$post->id.'/report')
            ->assertOk()->assertJsonPath('data.is_hidden', true);

        $this->getJson('/api/hitokoto')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_it_requires_admin_token_to_delete_post(): void
    {
        $post = HitokotoPost::query()->create(['body' => '削除対象']);

        $this->deleteJson('/api/hitokoto/'.$post->id)->assertUnauthorized();

        $this->assertDatabaseHas('hitokoto_posts', ['id' => $post->id]);
    }

    public function test_admin_can_delete_post(): void
    {
        $post = HitokotoPost::query()->create(['body' => '削除対象']);

        $response = $this->withHeaders($this->adminHeaders())
            ->deleteJson('/api/hitokoto/'.$post->id);

        $response->assertNoContent();
        $this->assertDatabaseMissing('hitokoto_posts', ['id' => $post->id]);
    }

    public function test_admin_can_hide_and_unhide_post(): void
    {
        $post = HitokotoPost::query()->create(['body' => '対象']);

        $this->withHeaders($this->adminHeaders())
            ->patchJson('/api/hitokoto/'.$post->id.'/hide')
            ->assertOk()
            ->assertJsonPath('data.is_hidden', true);

        $this->getJson('/api/hitokoto')->assertOk()->assertJsonCount(0, 'data');

        $this->withHeaders($this->adminHeaders())
            ->patchJson('/api/hitokoto/'.$post->id.'/unhide')
            ->assertOk()
            ->assertJsonPath('data.is_hidden', false);

        $this->getJson('/api/hitokoto')->assertOk()->assertJsonCount(1, 'data');
    }
}
