<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;
use App\Services\CloudinaryImageService;
use Illuminate\Http\JsonResponse;

class ArticleController extends Controller
{
    public function __construct(private readonly CloudinaryImageService $images) {}

    private function formatArticle(Article $article): array
    {
        return [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'image_url' => $article->image_url,
            'image_caption' => $article->image_caption,
            'body' => $article->body,
            'excerpt' => $article->excerpt,
            'type' => $article->type,
            'published_at' => $article->published_at,
            'is_public' => $article->is_public,
        ];
    }

    public function store(StoreArticleRequest $request): JsonResponse
    {
        $validated = $request->validated();
        unset($validated['image']);

        if ($request->hasFile('image')) {
            $validated = [
                ...$validated,
                ...$this->images->upload($request->file('image'), 'nichijobase/articles'),
            ];
        }

        $article = Article::query()->create([
            ...$validated,
            'is_public' => $request->boolean('is_public', true),
        ]);

        return response()->json([
            'data' => $this->formatArticle($article),
        ], 201);
    }

    public function update(UpdateArticleRequest $request, string $slug): JsonResponse
    {
        $article = Article::query()->where('slug', $slug)->firstOrFail();

        $validated = $request->validated();
        unset($validated['image']);

        if ($request->hasFile('image')) {
            $this->images->delete($article->image_public_id);
            $validated = [
                ...$validated,
                ...$this->images->upload($request->file('image'), 'nichijobase/articles'),
            ];
        }

        $article->fill($validated);

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
            ->withCount('comments')
            ->withMax('comments as latest_comment_at', 'created_at')
            ->published()
            ->orderByDesc('published_at')
            ->get([
                'id', 'title', 'slug', 'excerpt', 'image_url', 'image_caption', 'type', 'published_at',
                'like_count', 'empathy_count', 'useful_count',
            ]);

        return response()->json([
            'data' => $articles->map(fn (Article $article): array => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'image_url' => $article->image_url,
                'image_caption' => $article->image_caption,
                'type' => $article->type,
                'published_at' => $article->published_at,
                'board_thread_id' => $article->boardThread?->id,
                'comments_count' => $article->comments_count,
                'latest_comment_at' => $article->latest_comment_at,
                'like_count' => $article->like_count,
                'empathy_count' => $article->empathy_count,
                'useful_count' => $article->useful_count,
            ])->all(),
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $article = Article::query()
            ->withCount('comments')
            ->withMax('comments as latest_comment_at', 'created_at')
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
                'image_url' => $article->image_url,
                'image_caption' => $article->image_caption,
                'type' => $article->type,
                'published_at' => $article->published_at,
                'view_count' => $article->pageViews()->count(),
                'board_thread_id' => $article->boardThread?->id,
                'comments_count' => $article->comments_count,
                'latest_comment_at' => $article->latest_comment_at,
                'like_count' => $article->like_count,
                'empathy_count' => $article->empathy_count,
                'useful_count' => $article->useful_count,
            ],
        ]);
    }
}
