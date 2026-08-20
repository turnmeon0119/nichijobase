<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOgiriPromptRequest;
use App\Models\OgiriPrompt;
use App\Services\CloudinaryImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminOgiriPageController extends Controller
{
    public function __construct(private readonly CloudinaryImageService $images) {}

    public function index(): View
    {
        $prompts = OgiriPrompt::query()
            ->withCount(['answers' => fn ($query) => $query->where('is_hidden', false)])
            ->orderByDesc('id')
            ->get(['id', 'title', 'body', 'image_url', 'published_at', 'is_public']);

        return view('admin.ogiri.index', [
            'prompts' => $prompts,
        ]);
    }

    public function create(): View
    {
        return view('admin.ogiri.form');
    }

    public function store(StoreOgiriPromptRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $image = $request->hasFile('image')
            ? $this->images->upload($request->file('image'))
            : [];

        OgiriPrompt::query()->create([
            ...$request->safe()->except('image'),
            ...$image,
            'is_public' => $request->boolean('is_public'),
            'published_at' => $validated['published_at'] ?? now(),
        ]);

        return redirect()
            ->route('admin.ogiri.index')
            ->with('status', '大喜利のお題を作成しました。');
    }

    public function destroy(OgiriPrompt $prompt): RedirectResponse
    {
        $this->images->delete($prompt->image_public_id);
        $prompt->delete();

        return redirect()
            ->route('admin.ogiri.index')
            ->with('status', '大喜利のお題を削除しました。');
    }
}
