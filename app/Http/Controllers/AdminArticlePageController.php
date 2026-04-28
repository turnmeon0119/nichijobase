<?php

namespace App\Http\Controllers;

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
        ]);
    }

    public function destroy(Article $article): RedirectResponse
    {
        $article->delete();

        return redirect()
            ->route('admin.articles.index')
            ->with('status', '記事を削除しました。');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('admin_web_token');

        return redirect()->route('admin.articles.login');
    }
}
