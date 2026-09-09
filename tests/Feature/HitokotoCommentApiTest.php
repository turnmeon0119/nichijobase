<?php

namespace Tests\Feature;

use App\Models\HitokotoPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HitokotoCommentApiTest extends TestCase
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

    public function test_it_creates_comment_without_auth(): void
    {
        $post = HitokotoPost::query()->create(['body' => '元投稿']);

        $response = $this->postJson('/api/hitokoto/'.$post->id.'/comments', [
            'name' => '名無し',
            'body' => 'いいですね',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.body', 'いいですね')
            ->assertJsonPath('data.name', '名無し')
            ->assertJsonPath('data.reports_count', 0);

        $this->assertDatabaseHas('hitokoto_comments', [
            'hitokoto_post_id' => $post->id,
            'body' => 'いいですね',
        ]);
    }

    public function test_it_defaults_blank_name_to_null(): void
    {
        $post = HitokotoPost::query()->create(['body' => '元投稿']);

        $response = $this->postJson('/api/hitokoto/'.$post->id.'/comments', [
            'name' => '',
            'body' => '名前なしのコメント',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', null);
    }

    public function test_it_rejects_body_over_200_characters(): void
    {
        $post = HitokotoPost::query()->create(['body' => '元投稿']);

        $response = $this->postJson('/api/hitokoto/'.$post->id.'/comments', [
            'body' => str_repeat('あ', 201),
        ]);

        $response->assertUnprocessable();
    }

    public function test_it_rejects_missing_body(): void
    {
        $post = HitokotoPost::query()->create(['body' => '元投稿']);

        $response = $this->postJson('/api/hitokoto/'.$post->id.'/comments', []);

        $response->assertUnprocessable();
    }

    public function test_it_increments_post_comments_count_on_create(): void
    {
        $post = HitokotoPost::query()->create(['body' => '元投稿']);

        $this->postJson('/api/hitokoto/'.$post->id.'/comments', [
            'body' => 'コメント1',
        ])->assertCreated();

        $this->assertDatabaseHas('hitokoto_posts', [
            'id' => $post->id,
            'comments_count' => 1,
        ]);
    }

    public function test_it_lists_comments_oldest_first(): void
    {
        $post = HitokotoPost::query()->create(['body' => '元投稿']);
        $first = $post->comments()->create(['body' => '1件目', 'reports_count' => 0]);
        $second = $post->comments()->create(['body' => '2件目', 'reports_count' => 0]);

        $response = $this->getJson('/api/hitokoto/'.$post->id.'/comments');

        $response->assertOk()
            ->assertJsonPath('data.0.id', $first->id)
            ->assertJsonPath('data.1.id', $second->id)
            ->assertJsonStructure([
                'data' => ['*' => ['id', 'name', 'body', 'created_at', 'reports_count']],
            ]);
    }

    public function test_it_excludes_hidden_comments_from_list(): void
    {
        $post = HitokotoPost::query()->create(['body' => '元投稿']);
        $post->comments()->create(['body' => '表示される', 'is_hidden' => false, 'reports_count' => 0]);
        $post->comments()->create(['body' => '非表示', 'is_hidden' => true, 'reports_count' => 0]);

        $this->getJson('/api/hitokoto/'.$post->id.'/comments')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_it_reports_comment_without_auth(): void
    {
        $post = HitokotoPost::query()->create(['body' => '元投稿']);
        $comment = $post->comments()->create(['body' => '通報対象', 'reports_count' => 0]);

        $response = $this->postJson('/api/hitokoto/comments/'.$comment->id.'/report');

        $response->assertOk()
            ->assertJsonPath('data.id', $comment->id)
            ->assertJsonPath('data.reports_count', 1);

        $this->assertDatabaseHas('hitokoto_comments', [
            'id' => $comment->id,
            'reports_count' => 1,
        ]);
    }

    public function test_it_hides_comment_after_three_reports(): void
    {
        $post = HitokotoPost::query()->create(['body' => '元投稿']);
        $comment = $post->comments()->create(['body' => '自動非表示対象', 'reports_count' => 0]);

        $this->postJson('/api/hitokoto/comments/'.$comment->id.'/report')
            ->assertOk()->assertJsonPath('data.is_hidden', false);
        $this->postJson('/api/hitokoto/comments/'.$comment->id.'/report')
            ->assertOk()->assertJsonPath('data.is_hidden', false);
        $this->postJson('/api/hitokoto/comments/'.$comment->id.'/report')
            ->assertOk()->assertJsonPath('data.is_hidden', true);

        $this->getJson('/api/hitokoto/'.$post->id.'/comments')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_it_requires_admin_token_to_delete_comment(): void
    {
        $post = HitokotoPost::query()->create(['body' => '元投稿']);
        $comment = $post->comments()->create(['body' => '削除対象', 'reports_count' => 0]);

        $this->deleteJson('/api/hitokoto/comments/'.$comment->id)->assertUnauthorized();

        $this->assertDatabaseHas('hitokoto_comments', ['id' => $comment->id]);
    }

    public function test_admin_can_delete_comment(): void
    {
        $post = HitokotoPost::query()->create(['body' => '元投稿']);
        $comment = $post->comments()->create(['body' => '削除対象', 'reports_count' => 0]);

        $response = $this->withHeaders($this->adminHeaders())
            ->deleteJson('/api/hitokoto/comments/'.$comment->id);

        $response->assertNoContent();
        $this->assertDatabaseMissing('hitokoto_comments', ['id' => $comment->id]);
    }

    public function test_admin_can_hide_and_unhide_comment(): void
    {
        $post = HitokotoPost::query()->create(['body' => '元投稿']);
        $comment = $post->comments()->create(['body' => '対象', 'reports_count' => 0]);

        $this->withHeaders($this->adminHeaders())
            ->patchJson('/api/hitokoto/comments/'.$comment->id.'/hide')
            ->assertOk()
            ->assertJsonPath('data.is_hidden', true);

        $this->getJson('/api/hitokoto/'.$post->id.'/comments')->assertOk()->assertJsonCount(0, 'data');

        $this->withHeaders($this->adminHeaders())
            ->patchJson('/api/hitokoto/comments/'.$comment->id.'/unhide')
            ->assertOk()
            ->assertJsonPath('data.is_hidden', false);

        $this->getJson('/api/hitokoto/'.$post->id.'/comments')->assertOk()->assertJsonCount(1, 'data');
    }
}
