<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;
use Illuminate\Http\JsonResponse;

class ArticleController extends Controller
{
    private function formatArticle(Article $article): array
    {
        return [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'body' => $article->body,
            'excerpt' => $article->excerpt,
            'type' => $article->type,
            'published_at' => $article->published_at,
            'is_public' => $article->is_public,
        ];
    }

    public function store(StoreArticleRequest $request): JsonResponse
    {
        $article = Article::query()->create([
            ...$request->validated(),
            'is_public' => $request->boolean('is_public', true),
        ]);

        return response()->json([
            'data' => $this->formatArticle($article),
        ], 201);
    }

    public function update(UpdateArticleRequest $request, string $slug): JsonResponse
    {
        $article = Article::query()->where('slug', $slug)->firstOrFail();

        $article->fill($request->validated());

        if ($request->has('is_public')) {
            $article->is_public = $request->boolean('is_public');
        }

        $article->save();

        return response()->json([
            'data' => $this->formatArticle($article->fresh()),
        ]);
    }

    public function destroy(string $slug): JsonResponse
    {
        $article = Article::query()->where('slug', $slug)->firstOrFail();
        $article->delete();

        return response()->json([], 204);
    }

    public function destroyById(Article $article): JsonResponse
    {
        $article->delete();

        return response()->json([], 204);
    }

    public function index(): JsonResponse
    {
        $articles = Article::query()
            ->with('boardThread:id,article_id')
            ->published()
            ->orderByDesc('published_at')
            ->get(['id', 'title', 'slug', 'excerpt', 'type', 'published_at']);

        return response()->json([
            'data' => $articles->map(fn (Article $article): array => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'type' => $article->type,
                'published_at' => $article->published_at,
                'board_thread_id' => $article->boardThread?->id,
            ])->all(),
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $article = Article::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $article->pageViews()->create();

        return response()->json([
            'data' => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'body' => $article->body,
                'excerpt' => $article->excerpt,
                'type' => $article->type,
                'published_at' => $article->published_at,
                'view_count' => $article->pageViews()->count(),
                'board_thread_id' => $article->boardThread?->id,
            ],
        ]);
    }
}
