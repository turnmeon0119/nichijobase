<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleReactionController extends Controller
{
    public function store(Request $request, string $slug): JsonResponse
    {
        $article = Article::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $validated = $request->validate([
            'type' => ['required', 'in:like,empathy,useful'],
        ]);

        $column = $validated['type'].'_count';
        $article->increment($column);
        $article->refresh();

        return response()->json([
            'data' => [
                'like_count' => $article->like_count,
                'empathy_count' => $article->empathy_count,
                'useful_count' => $article->useful_count,
            ],
        ]);
    }
}
