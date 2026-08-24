<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = NewsItem::query()
            ->published()
            ->orderByDesc('published_at')
            ->orderByDesc('id');

        if ($request->has('page') || $request->has('per_page')) {
            $perPage = min(max((int) $request->integer('per_page', 10), 1), 30);
            $paginator = $query->paginate($perPage);

            return response()->json([
                'data' => $paginator->getCollection()->map(fn (NewsItem $item): array => $this->formatNewsItem($item))->all(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ]);
        }

        $items = $query
            ->get()
            ->map(fn (NewsItem $item): array => $this->formatNewsItem($item));

        return response()->json(['data' => $items]);
    }

    public function show(string $slug): JsonResponse
    {
        $item = NewsItem::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'data' => $this->formatNewsItem($item),
        ]);
    }

    private function formatNewsItem(NewsItem $item): array
    {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'slug' => $item->slug,
            'body' => $item->body,
            'published_at' => $item->published_at,
        ];
    }
}
