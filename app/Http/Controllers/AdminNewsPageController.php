<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNewsItemRequest;
use App\Http\Requests\UpdateNewsItemRequest;
use App\Models\NewsItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $validated = $request->validated();
        $saveMode = (string) $request->input('save_mode', 'save');
        $isPublic = $this->resolveIsPublic($request, $validated, $saveMode);
        unset($validated['save_mode']);

        $item = NewsItem::query()->create([
            ...$validated,
            'is_public' => $isPublic,
        ]);

        return redirect()
            ->route('admin.news.edit', $item)
            ->with('status', $this->statusMessage('News', $saveMode, 'Newsを作成しました。'));
    }

    public function edit(NewsItem $newsItem): View
    {
        return view('admin.news.form', [
            'item' => $newsItem,
        ]);
    }

    public function update(UpdateNewsItemRequest $request, NewsItem $newsItem): RedirectResponse
    {
        $validated = $request->validated();
        $saveMode = (string) $request->input('save_mode', 'save');
        $isPublic = $this->resolveIsPublic($request, $validated, $saveMode);
        unset($validated['save_mode']);

        $newsItem->update([
            ...$validated,
            'is_public' => $isPublic,
        ]);

        return redirect()
            ->route('admin.news.edit', $newsItem)
            ->with('status', $this->statusMessage('News', $saveMode, 'Newsを更新しました。'));
    }

    public function destroy(NewsItem $newsItem): RedirectResponse
    {
        $newsItem->delete();

        return redirect()
            ->route('admin.news.index')
            ->with('status', 'Newsを削除しました。');
    }

    /**
     * @param array<string, mixed> $validated
     */
    private function resolveIsPublic(Request $request, array &$validated, string $saveMode): bool
    {
        if ($saveMode === 'draft') {
            return false;
        }

        if ($saveMode === 'publish') {
            if (empty($validated['published_at'])) {
                $validated['published_at'] = now();
            }

            return true;
        }

        return $request->boolean('is_public');
    }

    private function statusMessage(string $resource, string $saveMode, string $default): string
    {
        return match ($saveMode) {
            'draft' => "{$resource}を下書き保存しました。",
            'publish' => "{$resource}を公開保存しました。",
            default => $default,
        };
    }
}
