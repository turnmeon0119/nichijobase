<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\BoardPost;
use App\Models\BoardThread;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BoardPageController extends Controller
{
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
                'article' => $post->thread?->article,
                'posts_count' => null,
            ]))
            ->sortByDesc('created_at')
            ->values();

        return view('board.timeline', [
            'items' => $items,
        ]);
    }

    public function index(): View
    {
        $threads = BoardThread::query()
            ->where('is_hidden', false)
            ->with('article:id,slug,title')
            ->withCount(['posts as posts_count' => fn ($query) => $query->where('is_hidden', false)])
            ->withMax(['posts as latest_post_at' => fn ($query) => $query->where('is_hidden', false)], 'created_at')
            ->orderByDesc('updated_at')
            ->get(['id', 'article_id', 'title', 'name', 'body', 'created_at']);

        $articles = Article::query()
            ->published()
            ->orderByDesc('published_at')
            ->get(['id', 'title', 'slug']);

        return view('board.index', [
            'threads' => $threads,
            'articles' => $articles,
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

        return redirect()
            ->route('board.show', $thread)
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

        $thread->posts()->create([
            ...$validated,
            'name' => ($validated['name'] ?? null) ?: null,
            'created_ip' => $request->ip(),
        ]);

        return redirect()
            ->route('board.show', $thread)
            ->with('status', '返信を投稿しました。');
    }
}
