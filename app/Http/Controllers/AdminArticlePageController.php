<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;
use App\Models\ArticleBlock;
use App\Services\CloudinaryImageService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class AdminArticlePageController extends Controller
{
    public function __construct(private readonly CloudinaryImageService $images) {}

    public function dashboard(): View
    {
        return view('admin.dashboard');
    }

    public function login(): View
    {
        return view('admin.articles.login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
        ]);

        if (!hash_equals((string) config('app.admin_api_token'), $validated['token'])) {
            return back()
                ->withErrors(['token' => 'トークンが違います。'])
                ->onlyInput('token');
        }

        $request->session()->put('admin_web_token', $validated['token']);

        return redirect()->route('admin.dashboard');
    }

    public function index(Request $request): View
    {
        $statusFilter = $this->publicationStatusFilter($request);
        $query = Article::query()
            ->with('boardThread:id,article_id')
            ->withCount('comments');

        $this->applyPublicationStatusFilter($query, $statusFilter);

        $articles = $query
            ->orderByDesc('id')
            ->get(['id', 'title', 'slug', 'type', 'published_at', 'is_public']);

        return view('admin.articles.index', [
            'articles' => $articles,
            'showingTrash' => false,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function trash(): View
    {
        $articles = Article::query()
            ->onlyTrashed()
            ->with('boardThread:id,article_id')
            ->withCount('comments')
            ->orderByDesc('deleted_at')
            ->get(['id', 'title', 'slug', 'type', 'published_at', 'is_public', 'deleted_at']);

        return view('admin.articles.index', [
            'articles' => $articles,
            'showingTrash' => true,
            'statusFilter' => 'trash',
        ]);
    }

    public function create(): View
    {
        return view('admin.articles.form', [
            'article' => null,
        ]);
    }

    public function store(StoreArticleRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $blocks = $validated['blocks'] ?? [];
        $saveMode = (string) $request->input('save_mode', 'save');
        $isPublic = $this->resolveIsPublic($request, $validated, $saveMode);
        unset($validated['image'], $validated['save_mode'], $validated['blocks']);

        $validated['body'] = $this->bodyFromBlocks($blocks, $validated['body'] ?? null);

        if ($request->hasFile('image')) {
            $validated = [
                ...$validated,
                ...$this->images->upload($request->file('image'), 'nichijobase/articles'),
            ];
        }

        $article = Article::query()->create([
            ...$validated,
            'is_public' => $isPublic,
        ]);

        $this->syncBlocks($article, $blocks);

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('status', $this->statusMessage('記事', $saveMode, '記事を作成しました。'));
    }

    public function edit(Article $article): View
    {
        return view('admin.articles.form', [
            'article' => $article->load('blocks'),
        ]);
    }

    public function update(UpdateArticleRequest $request, Article $article): RedirectResponse
    {
        $validated = $request->validated();
        $blocks = $validated['blocks'] ?? [];
        $saveMode = (string) $request->input('save_mode', 'save');
        $isPublic = $this->resolveIsPublic($request, $validated, $saveMode);
        unset($validated['image'], $validated['save_mode'], $validated['blocks']);

        $validated['body'] = $this->bodyFromBlocks($blocks, $validated['body'] ?? $article->body);

        if ($request->hasFile('image')) {
            $this->images->delete($article->image_public_id);
            $validated = [
                ...$validated,
                ...$this->images->upload($request->file('image'), 'nichijobase/articles'),
            ];
        }

        $article->update([
            ...$validated,
            'is_public' => $isPublic,
        ]);

        $this->syncBlocks($article, $blocks);

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('status', $this->statusMessage('記事', $saveMode, '記事を更新しました。'));
    }

    public function destroy(Article $article): RedirectResponse
    {
        $article->delete();

        return redirect()
            ->route('admin.articles.index')
            ->with('status', '記事をゴミ箱へ移動しました。');
    }

    public function restore(int $article): RedirectResponse
    {
        $target = Article::query()->onlyTrashed()->findOrFail($article);
        $target->restore();

        return redirect()
            ->route('admin.articles.trash')
            ->with('status', '記事を復元しました。');
    }

    public function forceDestroy(int $article): RedirectResponse
    {
        $target = Article::query()->onlyTrashed()->findOrFail($article);
        $target->forceDelete();

        return redirect()
            ->route('admin.articles.trash')
            ->with('status', '記事を完全に削除しました。');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('admin_web_token');

        return redirect()->route('admin.articles.login');
    }

    /**
     * @param array<string, mixed> $validated
     */
    private function resolveIsPublic(Request $request, array &$validated, string $saveMode): bool
    {
        if ($saveMode === 'draft') {
            return false;
        }

        if ($saveMode === 'publish') {
            if (empty($validated['published_at'])) {
                $validated['published_at'] = now();
            }

            return true;
        }

        return $request->boolean('is_public');
    }

    private function statusMessage(string $resource, string $saveMode, string $default): string
    {
        return match ($saveMode) {
            'draft' => "{$resource}を下書き保存しました。",
            'publish' => "{$resource}を公開保存しました。",
            default => $default,
        };
    }

    private function publicationStatusFilter(Request $request): string
    {
        $status = (string) $request->query('status', 'all');

        return in_array($status, ['all', 'published', 'draft', 'scheduled'], true)
            ? $status
            : 'all';
    }

    private function applyPublicationStatusFilter(Builder $query, string $status): void
    {
        match ($status) {
            'published' => $query
                ->where('is_public', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now()),
            'draft' => $query->where('is_public', false),
            'scheduled' => $query
                ->where('is_public', true)
                ->whereNotNull('published_at')
                ->where('published_at', '>', now()),
            default => null,
        };
    }

    /**
     * @param array<int, array<string, mixed>> $blocks
     */
    private function bodyFromBlocks(array $blocks, ?string $fallback): string
    {
        $body = collect($blocks)
            ->filter(fn (array $block): bool => ($block['type'] ?? null) === 'text')
            ->map(fn (array $block): string => trim((string) ($block['body'] ?? '')))
            ->filter()
            ->implode("\n\n");

        return $body !== '' ? $body : trim((string) $fallback);
    }

    /**
     * @param array<int, array<string, mixed>> $blocks
     */
    private function syncBlocks(Article $article, array $blocks): void
    {
        $existing = $article->blocks()->get()->keyBy('id');
        $keptIds = [];
        $sortOrder = 0;

        foreach (array_values($blocks) as $block) {
            $type = (string) ($block['type'] ?? '');
            $id = isset($block['id']) ? (int) $block['id'] : null;
            $target = $id ? $existing->get($id) : null;

            if ($target && $target->article_id !== $article->id) {
                continue;
            }

            if ($type === 'text') {
                $body = trim((string) ($block['body'] ?? ''));

                if ($body === '') {
                    if ($target instanceof ArticleBlock) {
                        $this->deleteBlockImage($target);
                        $target->delete();
                    }

                    continue;
                }

                if (! $target instanceof ArticleBlock) {
                    $target = new ArticleBlock(['article_id' => $article->id]);
                }

                $this->deleteBlockImage($target);
                $target->fill([
                    'type' => 'text',
                    'body' => $body,
                    'image_url' => null,
                    'image_public_id' => null,
                    'image_caption' => null,
                    'sort_order' => $sortOrder++,
                ]);
                $target->save();
                $keptIds[] = $target->id;

                continue;
            }

            if ($type === 'image') {
                $image = $block['image'] ?? null;

                if (! $target instanceof ArticleBlock && ! $image instanceof UploadedFile) {
                    continue;
                }

                if (! $target instanceof ArticleBlock) {
                    $target = new ArticleBlock(['article_id' => $article->id]);
                }

                $payload = [
                    'type' => 'image',
                    'body' => null,
                    'image_caption' => trim((string) ($block['image_caption'] ?? '')) ?: null,
                    'sort_order' => $sortOrder++,
                ];

                if ($image instanceof UploadedFile) {
                    $this->deleteBlockImage($target);
                    $payload = [
                        ...$payload,
                        ...$this->images->upload($image, 'nichijobase/articles/body'),
                    ];
                }

                $target->fill($payload);
                $target->save();
                $keptIds[] = $target->id;
            }
        }

        $deleteQuery = $article->blocks();

        if ($keptIds !== []) {
            $deleteQuery->whereNotIn('id', $keptIds);
        }

        $deleteQuery
            ->get()
            ->each(function (ArticleBlock $block): void {
                $this->deleteBlockImage($block);
                $block->delete();
            });
    }

    private function deleteBlockImage(ArticleBlock $block): void
    {
        if ($block->image_public_id) {
            $this->images->delete($block->image_public_id);
        }
    }
}
