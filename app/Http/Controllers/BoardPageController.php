<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\BoardPost;
use App\Models\BoardThread;
use App\Services\CloudinaryImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BoardPageController extends Controller
{
    public function __construct(private readonly CloudinaryImageService $images) {}

    private function formatTimestamp(?Carbon $timestamp): array
    {
        if ($timestamp === null) {
            return [
                'relative' => '',
                'absolute' => '',
            ];
        }

        return [
            'relative' => $timestamp->diffForHumans(),
            'absolute' => $timestamp->format('Y/m/d H:i'),
        ];
    }

    private function rememberedName(Request $request): string
    {
        return (string) $request->session()->get('board_name', '');
    }

    private function storeRememberedName(Request $request, ?string $name): void
    {
        $request->session()->put('board_name', trim((string) $name));
    }

    private function isAdmin(Request $request): bool
    {
        $expectedToken = (string) config('app.admin_api_token');
        $sessionToken = (string) $request->session()->get('admin_web_token', '');

        return $expectedToken !== '' && hash_equals($expectedToken, $sessionToken);
    }

    public function timeline(): View
    {
        $threads = BoardThread::query()
            ->where('is_hidden', false)
            ->with('article:id,slug,title')
            ->withCount(['posts as posts_count' => fn ($query) => $query->where('is_hidden', false)])
            ->get(['id', 'article_id', 'title', 'name', 'body', 'created_at']);

        $posts = BoardPost::query()
            ->where('is_hidden', false)
            ->with(['thread:id,title,article_id', 'thread.article:id,slug,title'])
            ->get(['id', 'board_thread_id', 'name', 'body', 'created_at']);

        $items = $threads
            ->map(fn (BoardThread $thread): array => [
                'kind' => 'thread',
                'id' => $thread->id,
                'thread_id' => $thread->id,
                'title' => $thread->title,
                'name' => $thread->name,
                'body' => $thread->body,
                'created_at' => $thread->created_at,
                'created_label' => $this->formatTimestamp($thread->created_at)['relative'],
                'created_exact' => $this->formatTimestamp($thread->created_at)['absolute'],
                'article' => $thread->article,
                'posts_count' => $thread->posts_count,
            ])
            ->concat($posts->map(fn (BoardPost $post): array => [
                'kind' => 'post',
                'id' => $post->id,
                'thread_id' => $post->board_thread_id,
                'title' => $post->thread?->title,
                'name' => $post->name,
                'body' => $post->body,
                'created_at' => $post->created_at,
                'created_label' => $this->formatTimestamp($post->created_at)['relative'],
                'created_exact' => $this->formatTimestamp($post->created_at)['absolute'],
                'article' => $post->thread?->article,
                'posts_count' => null,
            ]))
            ->sortByDesc('created_at')
            ->values();

        return view('board.timeline', [
            'items' => $items,
            'isAdmin' => $this->isAdmin(request()),
        ]);
    }

    public function index(Request $request): View
    {
        $statusFilter = $this->boardStatusFilter($request);
        $query = BoardThread::query()
            ->with('article:id,slug,title')
            ->withCount(['posts as posts_count' => fn ($query) => $query->where('is_hidden', false)])
            ->withMax(['posts as latest_post_at' => fn ($query) => $query->where('is_hidden', false)], 'created_at');

        match ($statusFilter) {
            'visible' => $query->where('is_hidden', false),
            'hidden' => $query->where('is_hidden', true),
            default => null,
        };

        $threads = $query
            ->orderByDesc('updated_at')
            ->get(['id', 'article_id', 'title', 'name', 'body', 'is_hidden', 'created_at']);

        $articles = Article::query()
            ->published()
            ->orderByDesc('published_at')
            ->get(['id', 'title', 'slug']);

        return view('board.index', [
            'threads' => $threads,
            'articles' => $articles,
            'rememberedName' => $this->rememberedName(request()),
            'isAdmin' => $this->isAdmin(request()),
            'statusFilter' => $statusFilter,
        ]);
    }

    public function show(BoardThread $thread): View
    {
        if ($thread->is_hidden) {
            throw new NotFoundHttpException();
        }

        $thread->load([
            'article:id,slug,title',
            'posts' => fn ($query) => $query->where('is_hidden', false)->orderBy('id'),
        ]);

        return view('board.show', [
            'thread' => $thread,
            'rememberedName' => $this->rememberedName(request()),
            'isAdmin' => $this->isAdmin(request()),
        ]);
    }

    public function storeThread(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'article_id' => ['nullable', 'integer', 'exists:articles,id'],
            'title' => ['required', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $thread = BoardThread::query()->create([
            ...$validated,
            'name' => ($validated['name'] ?? null) ?: null,
            'created_ip' => $request->ip(),
        ]);
        $this->storeRememberedName($request, $validated['name'] ?? null);

        return redirect()
            ->to(route('admin.board.show', $thread).'#thread-'.$thread->id)
            ->with('status', 'スレッドを作成しました。');
    }

    public function storePost(Request $request, BoardThread $thread): RedirectResponse
    {
        if ($thread->is_hidden) {
            throw new NotFoundHttpException();
        }

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $post = $thread->posts()->create([
            ...$validated,
            'name' => ($validated['name'] ?? null) ?: null,
            'created_ip' => $request->ip(),
        ]);
        $this->storeRememberedName($request, $validated['name'] ?? null);

        return redirect()
            ->to(route('admin.board.show', $thread).'#post-'.$post->id)
            ->with('status', '返信を投稿しました。');
    }

    public function destroy(BoardThread $thread): RedirectResponse
    {
        $thread->load('posts:id,board_thread_id,image_public_id');
        foreach ($thread->posts as $post) {
            $this->images->delete($post->image_public_id);
        }
        $this->images->delete($thread->image_public_id);
        $thread->delete();

        return redirect()
            ->route('admin.board.index')
            ->with('status', 'スレッドを削除しました。');
    }

    private function boardStatusFilter(Request $request): string
    {
        $status = (string) $request->query('status', 'visible');

        return in_array($status, ['visible', 'hidden', 'all'], true)
            ? $status
            : 'visible';
    }
}
