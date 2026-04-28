<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBoardThreadRequest;
use App\Models\Article;
use App\Models\BoardThread;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BoardThreadController extends Controller
{
    private function formatThread(BoardThread $thread, bool $includePosts = false): array
    {
        $payload = [
            'id' => $thread->id,
            'article_id' => $thread->article_id,
            'title' => $thread->title,
            'name' => $thread->name,
            'body' => $thread->body,
            'created_at' => $thread->created_at,
            'reports_count' => $thread->reports_count,
            'article' => $thread->article ? [
                'id' => $thread->article->id,
                'slug' => $thread->article->slug,
                'title' => $thread->article->title,
            ] : null,
        ];

        if ($includePosts) {
            $payload['posts'] = $thread->posts->map(fn ($post): array => [
                'id' => $post->id,
                'name' => $post->name,
                'body' => $post->body,
                'created_at' => $post->created_at,
            ])->all();
        }

        return $payload;
    }

    public function index(): JsonResponse
    {
        $threads = BoardThread::query()
            ->where('is_hidden', false)
            ->with('article:id,slug,title')
            ->withCount(['posts as posts_count' => fn ($query) => $query->where('is_hidden', false)])
            ->withMax(['posts as latest_post_at' => fn ($query) => $query->where('is_hidden', false)], 'created_at')
            ->orderByDesc('updated_at')
            ->get(['id', 'article_id', 'title', 'name', 'body', 'created_at', 'reports_count']);

        return response()->json([
            'data' => $threads->map(fn (BoardThread $thread): array => [
                ...$this->formatThread($thread),
                'posts_count' => $thread->posts_count,
                'latest_post_at' => $thread->latest_post_at,
            ])->all(),
        ]);
    }

    public function show(BoardThread $thread): JsonResponse
    {
        if ($thread->is_hidden) {
            throw new NotFoundHttpException();
        }

        $thread->load([
            'article:id,slug,title',
            'posts' => fn ($query) => $query->where('is_hidden', false)->orderBy('id'),
        ]);

        return response()->json([
            'data' => $this->formatThread($thread, true),
        ]);
    }

    public function store(StoreBoardThreadRequest $request): JsonResponse
    {
        $thread = BoardThread::query()->create([
            ...$request->validated(),
            'name' => $request->input('name') ?: null,
            'created_ip' => $request->ip(),
        ]);
        $thread->load('article:id,slug,title');

        return response()->json([
            'data' => $this->formatThread($thread),
        ], 201);
    }

    public function showByArticle(string $slug): JsonResponse
    {
        $article = Article::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail(['id']);

        $thread = BoardThread::query()
            ->with([
                'article:id,slug,title',
                'posts' => fn ($query) => $query->where('is_hidden', false)->orderBy('id'),
            ])
            ->where('article_id', $article->id)
            ->where('is_hidden', false)
            ->first();

        return response()->json([
            'data' => $thread ? $this->formatThread($thread, true) : null,
        ]);
    }

    public function destroy(BoardThread $thread): JsonResponse
    {
        $thread->delete();

        return response()->json([], 204);
    }

    public function report(BoardThread $thread): JsonResponse
    {
        $thread->increment('reports_count');

        return response()->json([
            'data' => [
                'id' => $thread->id,
                'reports_count' => $thread->reports_count,
            ],
        ]);
    }

    public function hide(BoardThread $thread): JsonResponse
    {
        $thread->is_hidden = true;
        $thread->save();

        return response()->json([
            'data' => [
                'id' => $thread->id,
                'is_hidden' => true,
            ],
        ]);
    }

    public function unhide(BoardThread $thread): JsonResponse
    {
        $thread->is_hidden = false;
        $thread->save();

        return response()->json([
            'data' => [
                'id' => $thread->id,
                'is_hidden' => false,
            ],
        ]);
    }
}
