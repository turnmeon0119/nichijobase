<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHitokotoCommentRequest;
use App\Models\HitokotoComment;
use App\Models\HitokotoPost;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HitokotoCommentController extends Controller
{
    private function formatComment(HitokotoComment $comment): array
    {
        return [
            'id' => $comment->id,
            'name' => $comment->name,
            'body' => $comment->body,
            'created_at' => $comment->created_at,
            'reports_count' => $comment->reports_count,
        ];
    }

    public function index(HitokotoPost $hitokotoPost): JsonResponse
    {
        $comments = $hitokotoPost->comments()
            ->where('is_hidden', false)
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $comments->map(fn (HitokotoComment $comment): array => $this->formatComment($comment))->all(),
        ]);
    }

    public function store(StoreHitokotoCommentRequest $request, HitokotoPost $hitokotoPost): JsonResponse
    {
        $comment = $hitokotoPost->comments()->create([
            ...$request->validated(),
            'name' => $request->input('name') ?: null,
            'created_ip' => $request->ip(),
            'reports_count' => 0,
        ]);

        $hitokotoPost->increment('comments_count');

        return response()->json([
            'data' => $this->formatComment($comment),
        ], 201);
    }

    public function report(HitokotoComment $hitokotoComment): JsonResponse
    {
        if ($hitokotoComment->is_hidden) {
            throw new NotFoundHttpException;
        }

        $hitokotoComment->increment('reports_count');
        $hitokotoComment->refresh();

        if ($hitokotoComment->reports_count >= 3) {
            $hitokotoComment->is_hidden = true;
            $hitokotoComment->save();
        }

        return response()->json([
            'data' => [
                'id' => $hitokotoComment->id,
                'reports_count' => $hitokotoComment->reports_count,
                'is_hidden' => $hitokotoComment->is_hidden,
            ],
        ]);
    }

    public function destroy(HitokotoComment $hitokotoComment): JsonResponse
    {
        $hitokotoComment->delete();

        return response()->json([], 204);
    }

    public function hide(HitokotoComment $hitokotoComment): JsonResponse
    {
        $hitokotoComment->is_hidden = true;
        $hitokotoComment->save();

        return response()->json([
            'data' => [
                'id' => $hitokotoComment->id,
                'is_hidden' => true,
            ],
        ]);
    }

    public function unhide(HitokotoComment $hitokotoComment): JsonResponse
    {
        $hitokotoComment->is_hidden = false;
        $hitokotoComment->save();

        return response()->json([
            'data' => [
                'id' => $hitokotoComment->id,
                'is_hidden' => false,
            ],
        ]);
    }
}
