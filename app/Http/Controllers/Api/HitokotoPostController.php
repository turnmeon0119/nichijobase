<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHitokotoPostRequest;
use App\Models\HitokotoPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HitokotoPostController extends Controller
{
    private function formatPost(HitokotoPost $post): array
    {
        return [
            'id' => $post->id,
            'name' => $post->name,
            'body' => $post->body,
            'created_at' => $post->created_at,
            'reports_count' => $post->reports_count,
            'pow_count' => $post->pow_count,
            'comments_count' => $post->comments_count,
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 30), 1), 50);

        $paginator = HitokotoPost::query()
            ->where('is_hidden', false)
            ->orderByDesc('id')
            ->paginate($perPage, ['id', 'name', 'body', 'created_at', 'reports_count', 'pow_count', 'comments_count']);

        return response()->json([
            'data' => $paginator->getCollection()->map(fn (HitokotoPost $post): array => $this->formatPost($post))->all(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function store(StoreHitokotoPostRequest $request): JsonResponse
    {
        $post = HitokotoPost::query()->create([
            ...$request->validated(),
            'name' => $request->input('name') ?: null,
            'created_ip' => $request->ip(),
            'reports_count' => 0,
            'pow_count' => 0,
            'comments_count' => 0,
        ]);

        return response()->json([
            'data' => $this->formatPost($post),
        ], 201);
    }

    public function report(HitokotoPost $hitokotoPost): JsonResponse
    {
        if ($hitokotoPost->is_hidden) {
            throw new NotFoundHttpException;
        }

        $hitokotoPost->increment('reports_count');
        $hitokotoPost->refresh();

        if ($hitokotoPost->reports_count >= 3) {
            $hitokotoPost->is_hidden = true;
            $hitokotoPost->save();
        }

        return response()->json([
            'data' => [
                'id' => $hitokotoPost->id,
                'reports_count' => $hitokotoPost->reports_count,
                'is_hidden' => $hitokotoPost->is_hidden,
            ],
        ]);
    }

    public function pow(HitokotoPost $hitokotoPost): JsonResponse
    {
        $hitokotoPost->increment('pow_count');
        $hitokotoPost->refresh();

        return response()->json([
            'data' => [
                'id' => $hitokotoPost->id,
                'pow_count' => $hitokotoPost->pow_count,
            ],
        ]);
    }

    public function destroy(HitokotoPost $hitokotoPost): JsonResponse
    {
        $hitokotoPost->delete();

        return response()->json([], 204);
    }

    public function hide(HitokotoPost $hitokotoPost): JsonResponse
    {
        $hitokotoPost->is_hidden = true;
        $hitokotoPost->save();

        return response()->json([
            'data' => [
                'id' => $hitokotoPost->id,
                'is_hidden' => true,
            ],
        ]);
    }

    public function unhide(HitokotoPost $hitokotoPost): JsonResponse
    {
        $hitokotoPost->is_hidden = false;
        $hitokotoPost->save();

        return response()->json([
            'data' => [
                'id' => $hitokotoPost->id,
                'is_hidden' => false,
            ],
        ]);
    }
}
