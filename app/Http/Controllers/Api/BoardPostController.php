<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBoardPostRequest;
use App\Models\BoardThread;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BoardPostController extends Controller
{
    public function store(StoreBoardPostRequest $request, BoardThread $thread): JsonResponse
    {
        if ($thread->is_hidden) {
            throw new NotFoundHttpException();
        }

        $post = $thread->posts()->create([
            ...$request->validated(),
            'name' => $request->input('name') ?: null,
            'created_ip' => $request->ip(),
        ]);

        return response()->json([
            'data' => [
                'id' => $post->id,
                'board_thread_id' => $thread->id,
                'name' => $post->name,
                'body' => $post->body,
                'created_at' => $post->created_at,
            ],
        ], 201);
    }

    public function destroy(BoardThread $thread, int $post): JsonResponse
    {
        $target = $thread->posts()->whereKey($post)->firstOrFail();
        $target->delete();

        return response()->json([], 204);
    }

    public function report(BoardThread $thread, int $post): JsonResponse
    {
        if ($thread->is_hidden) {
            throw new NotFoundHttpException();
        }

        $target = $thread->posts()->whereKey($post)->where('is_hidden', false)->firstOrFail();
        $target->increment('reports_count');

        return response()->json([
            'data' => [
                'id' => $target->id,
                'reports_count' => $target->reports_count,
            ],
        ]);
    }

    public function hide(BoardThread $thread, int $post): JsonResponse
    {
        $target = $thread->posts()->whereKey($post)->firstOrFail();
        $target->is_hidden = true;
        $target->save();

        return response()->json([
            'data' => [
                'id' => $target->id,
                'is_hidden' => true,
            ],
        ]);
    }

    public function unhide(BoardThread $thread, int $post): JsonResponse
    {
        $target = $thread->posts()->whereKey($post)->firstOrFail();
        $target->is_hidden = false;
        $target->save();

        return response()->json([
            'data' => [
                'id' => $target->id,
                'is_hidden' => false,
            ],
        ]);
    }
}
