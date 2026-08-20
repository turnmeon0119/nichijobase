<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsItem;
use Illuminate\Http\JsonResponse;

class NewsItemController extends Controller
{
    public function index(): JsonResponse
    {
        $items = NewsItem::query()
            ->published()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
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
