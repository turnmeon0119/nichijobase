<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBoardThreadRequest;
use App\Models\Article;
use App\Models\BoardThread;
use App\Services\CloudinaryImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BoardThreadController extends Controller
{
    public function __construct(private readonly CloudinaryImageService $images) {}

    private function formatThread(BoardThread $thread, bool $includePosts = false): array
    {
        $payload = [
            'id' => $thread->id,
            'article_id' => $thread->article_id,
            'title' => $thread->title,
            'name' => $thread->name,
            'body' => $thread->body,
            'image_url' => $thread->image_url,
            'image_caption' => $thread->image_caption,
            'created_at' => $thread->created_at,
            'reports_count' => $thread->reports_count,
            'empathy_count' => $thread->empathy_count,
            'perspective_count' => $thread->perspective_count,
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
                'image_url' => $post->image_url,
                'image_caption' => $post->image_caption,
                'empathy_count' => $post->empathy_count,
                'perspective_count' => $post->perspective_count,
                'created_at' => $post->created_at,
            ])->all();
        }

        return $payload;
    }

    public function index(Request $request): JsonResponse
    {
        $sort = $request->validate([
            'sort' => ['sometimes', 'in:latest,popular'],
            'q' => ['sometimes', 'nullable', 'string', 'max:100'],
        ])['sort'] ?? 'latest';
        $keyword = trim((string) $request->query('q', ''));

        $query = BoardThread::query()
            ->where('is_hidden', false)
            ->with('article:id,slug,title')
            ->withCount(['posts as posts_count' => fn ($query) => $query->where('is_hidden', false)])
            ->withMax(['posts as latest_post_at' => fn ($query) => $query->where('is_hidden', false)], 'created_at');

        if ($keyword !== '') {
            $query->where(function ($query) use ($keyword): void {
                $query
                    ->where('title', 'like', '%'.$keyword.'%')
                    ->orWhere('body', 'like', '%'.$keyword.'%');
            });
        }

        if ($sort === 'popular') {
            $query->orderByRaw('(empathy_count + perspective_count) DESC');
        }

        $threads = $query
            ->orderByDesc('updated_at')
            ->get([
                'id', 'article_id', 'title', 'name', 'body', 'image_url', 'image_caption', 'created_at',
                'reports_count', 'empathy_count', 'perspective_count',
            ]);

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
        $image = $request->hasFile('image')
            ? $this->images->upload($request->file('image'))
            : [];

        $thread = BoardThread::query()->create([
            ...$request->safe()->except('image'),
            ...$image,
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
        $thread->load('posts:id,board_thread_id,image_public_id');
        foreach ($thread->posts as $post) {
            $this->images->delete($post->image_public_id);
        }
        $this->images->delete($thread->image_public_id);
        $thread->delete();

        return response()->json([], 204);
    }

    public function report(BoardThread $thread): JsonResponse
    {
        if ($thread->is_hidden) {
            throw new NotFoundHttpException();
        }

        $thread->increment('reports_count');
        $thread->refresh();

        if ($thread->reports_count >= 3) {
            $thread->is_hidden = true;
            $thread->save();
        }

        return response()->json([
            'data' => [
                'id' => $thread->id,
                'reports_count' => $thread->reports_count,
                'is_hidden' => $thread->is_hidden,
            ],
        ]);
    }

    public function react(Request $request, BoardThread $thread): JsonResponse
    {
        if ($thread->is_hidden) {
            throw new NotFoundHttpException();
        }

        $validated = $request->validate([
            'type' => ['required', 'in:empathy,perspective'],
        ]);
        $column = $validated['type'].'_count';

        $thread->increment($column);
        $thread->refresh();

        return response()->json([
            'data' => [
                'empathy_count' => $thread->empathy_count,
                'perspective_count' => $thread->perspective_count,
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
