<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNewsItemRequest;
use App\Http\Requests\UpdateNewsItemRequest;
use App\Models\NewsItem;
use App\Models\NewsItemBlock;
use App\Services\CloudinaryImageService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class AdminNewsPageController extends Controller
{
    public function __construct(private readonly CloudinaryImageService $images) {}

    public function index(Request $request): View
    {
        $statusFilter = $this->publicationStatusFilter($request);
        $query = NewsItem::query();

        $this->applyPublicationStatusFilter($query, $statusFilter);

        $items = $query
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        return view('admin.news.index', [
            'items' => $items,
            'statusFilter' => $statusFilter,
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
        $blocks = $validated['blocks'] ?? [];
        $saveMode = (string) $request->input('save_mode', 'save');
        $isPublic = $this->resolveIsPublic($request, $validated, $saveMode);
        unset($validated['save_mode'], $validated['blocks']);

        $validated['body'] = $this->bodyFromBlocks($blocks, $validated['body'] ?? null);

        $item = NewsItem::query()->create([
            ...$validated,
            'is_public' => $isPublic,
        ]);

        $this->syncBlocks($item, $blocks);

        return redirect()
            ->route('admin.news.edit', $item)
            ->with('status', $this->statusMessage('News', $saveMode, 'Newsを作成しました。'));
    }

    public function edit(NewsItem $newsItem): View
    {
        return view('admin.news.form', [
            'item' => $newsItem->load('blocks'),
        ]);
    }

    public function update(UpdateNewsItemRequest $request, NewsItem $newsItem): RedirectResponse
    {
        $validated = $request->validated();
        $blocks = $validated['blocks'] ?? [];
        $saveMode = (string) $request->input('save_mode', 'save');
        $isPublic = $this->resolveIsPublic($request, $validated, $saveMode);
        unset($validated['save_mode'], $validated['blocks']);

        $validated['body'] = $this->bodyFromBlocks($blocks, $validated['body'] ?? $newsItem->body);

        $newsItem->update([
            ...$validated,
            'is_public' => $isPublic,
        ]);

        $this->syncBlocks($newsItem, $blocks);

        return redirect()
            ->route('admin.news.edit', $newsItem)
            ->with('status', $this->statusMessage('News', $saveMode, 'Newsを更新しました。'));
    }

    public function destroy(NewsItem $newsItem): RedirectResponse
    {
        $newsItem->blocks()
            ->get()
            ->each(function (NewsItemBlock $block): void {
                $this->deleteBlockImage($block);
            });
        $newsItem->delete();

        return redirect()
            ->route('admin.news.index')
            ->with('status', 'Newsを削除しました。');
    }

    /**
     * @param  array<string, mixed>  $validated
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

    private function publicationStatusFilter(Request $request): string
    {
        $status = (string) $request->query('status', 'all');

        return in_array($status, ['all', 'published', 'draft', 'scheduled'], true)
            ? $status
            : 'all';
    }

    private function applyPublicationStatusFilter(Builder $query, string $status): void
    {
        match ($status) {
            'published' => $query
                ->where('is_public', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now()),
            'draft' => $query->where('is_public', false),
            'scheduled' => $query
                ->where('is_public', true)
                ->whereNotNull('published_at')
                ->where('published_at', '>', now()),
            default => null,
        };
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     */
    private function bodyFromBlocks(array $blocks, ?string $fallback): string
    {
        $body = collect($blocks)
            ->filter(fn (array $block): bool => ($block['type'] ?? null) === 'text')
            ->map(fn (array $block): string => trim((string) ($block['body'] ?? '')))
            ->filter()
            ->implode("\n\n");

        return $body !== '' ? $body : trim((string) $fallback);
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     */
    private function syncBlocks(NewsItem $item, array $blocks): void
    {
        $existing = $item->blocks()->get()->keyBy('id');
        $keptIds = [];
        $sortOrder = 0;

        foreach (array_values($blocks) as $block) {
            $type = (string) ($block['type'] ?? '');
            $id = isset($block['id']) ? (int) $block['id'] : null;
            $target = $id ? $existing->get($id) : null;

            if ($target && $target->news_item_id !== $item->id) {
                continue;
            }

            if ($type === 'text') {
                $body = trim((string) ($block['body'] ?? ''));

                if ($body === '') {
                    if ($target instanceof NewsItemBlock) {
                        $this->deleteBlockImage($target);
                        $target->delete();
                    }

                    continue;
                }

                if (! $target instanceof NewsItemBlock) {
                    $target = new NewsItemBlock(['news_item_id' => $item->id]);
                }

                $this->deleteBlockImage($target);
                $target->fill([
                    'type' => 'text',
                    'body' => $body,
                    'image_url' => null,
                    'image_public_id' => null,
                    'image_caption' => null,
                    'sort_order' => $sortOrder++,
                ]);
                $target->save();
                $keptIds[] = $target->id;

                continue;
            }

            if ($type === 'image') {
                $image = $block['image'] ?? null;

                if (! $target instanceof NewsItemBlock && ! $image instanceof UploadedFile) {
                    continue;
                }

                if (! $target instanceof NewsItemBlock) {
                    $target = new NewsItemBlock(['news_item_id' => $item->id]);
                }

                $payload = [
                    'type' => 'image',
                    'body' => null,
                    'image_caption' => trim((string) ($block['image_caption'] ?? '')) ?: null,
                    'sort_order' => $sortOrder++,
                ];

                if ($image instanceof UploadedFile) {
                    $this->deleteBlockImage($target);
                    $payload = [
                        ...$payload,
                        ...$this->images->upload($image, 'nichijobase/news/body'),
                    ];
                }

                $target->fill($payload);
                $target->save();
                $keptIds[] = $target->id;
            }
        }

        $deleteQuery = $item->blocks();

        if ($keptIds !== []) {
            $deleteQuery->whereNotIn('id', $keptIds);
        }

        $deleteQuery
            ->get()
            ->each(function (NewsItemBlock $block): void {
                $this->deleteBlockImage($block);
                $block->delete();
            });
    }

    private function deleteBlockImage(NewsItemBlock $block): void
    {
        if ($block->image_public_id) {
            $this->images->delete($block->image_public_id);
        }
    }
}
