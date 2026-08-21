<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleCommentRequest;
use App\Models\Article;
use App\Models\ArticleComment;
use Illuminate\Http\JsonResponse;

class ArticleCommentController extends Controller
{
    private function findArticle(string $slug): Article
    {
        return Article::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();
    }

    private function formatComment(ArticleComment $comment): array
    {
        return [
            'id' => $comment->id,
            'name' => $comment->name,
            'body' => $comment->body,
            'created_at' => $comment->created_at,
        ];
    }

    public function index(string $slug): JsonResponse
    {
        $article = $this->findArticle($slug);

        $comments = $article->comments()
            ->oldest('id')
            ->get();

        return response()->json([
            'data' => $comments->map(fn (ArticleComment $comment): array => $this->formatComment($comment))->all(),
        ]);
    }

    public function store(StoreArticleCommentRequest $request, string $slug): JsonResponse
    {
        $article = $this->findArticle($slug);

        $comment = $article->comments()->create([
            ...$request->validated(),
            'name' => $request->input('name') ?: null,
            'created_ip' => $request->ip(),
        ]);

        return response()->json([
            'data' => $this->formatComment($comment),
        ], 201);
    }
}
