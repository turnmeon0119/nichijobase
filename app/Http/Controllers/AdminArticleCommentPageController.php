<?php

namespace App\Http\Controllers;

use App\Models\ArticleComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminArticleCommentPageController extends Controller
{
    public function index(): View
    {
        $comments = ArticleComment::query()
            ->with('article:id,title,slug')
            ->latest('id')
            ->get();

        return view('admin.article-comments.index', [
            'comments' => $comments,
        ]);
    }

    public function destroy(ArticleComment $comment): RedirectResponse
    {
        $comment->delete();

        return redirect()
            ->route('admin.article-comments.index')
            ->with('status', '記事コメントを削除しました。');
    }
}
