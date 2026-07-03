<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminArticlePageController extends Controller
{
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

    public function index(): View
    {
        $articles = Article::query()
            ->with('boardThread:id,article_id')
            ->orderByDesc('id')
            ->get(['id', 'title', 'slug', 'type', 'published_at', 'is_public']);

        return view('admin.articles.index', [
            'articles' => $articles,
            'showingTrash' => false,
        ]);
    }

    public function trash(): View
    {
        $articles = Article::query()
            ->onlyTrashed()
            ->with('boardThread:id,article_id')
            ->orderByDesc('deleted_at')
            ->get(['id', 'title', 'slug', 'type', 'published_at', 'is_public', 'deleted_at']);

        return view('admin.articles.index', [
            'articles' => $articles,
            'showingTrash' => true,
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
        $article = Article::query()->create([
            ...$request->validated(),
            'is_public' => $request->boolean('is_public'),
        ]);

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('status', '記事を作成しました。');
    }

    public function edit(Article $article): View
    {
        return view('admin.articles.form', [
            'article' => $article,
        ]);
    }

    public function update(UpdateArticleRequest $request, Article $article): RedirectResponse
    {
        $article->update([
            ...$request->validated(),
            'is_public' => $request->boolean('is_public'),
        ]);

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('status', '記事を更新しました。');
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
}
