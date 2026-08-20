<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNewsItemRequest;
use App\Http\Requests\UpdateNewsItemRequest;
use App\Models\NewsItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminNewsPageController extends Controller
{
    public function index(): View
    {
        $items = NewsItem::query()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        return view('admin.news.index', [
            'items' => $items,
        ]);
    }

    public function create(): View
    {
        return view('admin.news.form', [
            'item' => null,
        ]);
    }

    public function store(StoreNewsItemRequest $request): RedirectResponse
    {
        $item = NewsItem::query()->create([
            ...$request->validated(),
            'is_public' => $request->boolean('is_public'),
        ]);

        return redirect()
            ->route('admin.news.edit', $item)
            ->with('status', 'Newsを作成しました。');
    }

    public function edit(NewsItem $newsItem): View
    {
        return view('admin.news.form', [
            'item' => $newsItem,
        ]);
    }

    public function update(UpdateNewsItemRequest $request, NewsItem $newsItem): RedirectResponse
    {
        $newsItem->update([
            ...$request->validated(),
            'is_public' => $request->boolean('is_public'),
        ]);

        return redirect()
            ->route('admin.news.edit', $newsItem)
            ->with('status', 'Newsを更新しました。');
    }

    public function destroy(NewsItem $newsItem): RedirectResponse
    {
        $newsItem->delete();

        return redirect()
            ->route('admin.news.index')
            ->with('status', 'Newsを削除しました。');
    }
}
