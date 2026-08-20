<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOgiriPromptRequest;
use App\Models\OgiriPrompt;
use App\Services\CloudinaryImageService;
use Illuminate\Http\JsonResponse;

class OgiriPromptController extends Controller
{
    public function __construct(private readonly CloudinaryImageService $images) {}

    public function index(): JsonResponse
    {
        $prompts = OgiriPrompt::query()
            ->published()
            ->withCount(['answers' => fn ($query) => $query->where('is_hidden', false)])
            ->latest('published_at')
            ->latest('id')
            ->get()
            ->map(fn (OgiriPrompt $prompt): array => $this->formatPrompt($prompt));

        return response()->json(['data' => $prompts]);
    }

    public function show(OgiriPrompt $prompt): JsonResponse
    {
        if (! $prompt->is_public || $prompt->published_at === null || $prompt->published_at->isFuture()) {
            abort(404);
        }

        $prompt->load([
            'answers' => fn ($query) => $query
                ->where('is_hidden', false)
                ->orderByDesc('funny_count')
                ->orderByDesc('genius_count')
                ->orderByDesc('id'),
        ]);

        return response()->json([
            'data' => $this->formatPrompt($prompt, includeAnswers: true),
        ]);
    }

    public function store(StoreOgiriPromptRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $image = $request->hasFile('image')
            ? $this->images->upload($request->file('image'))
            : [];

        $prompt = OgiriPrompt::query()->create([
            ...$request->safe()->except('image'),
            ...$image,
            'is_public' => $validated['is_public'] ?? true,
            'published_at' => $validated['published_at'] ?? now(),
        ]);

        return response()->json([
            'data' => $this->formatPrompt($prompt),
        ], 201);
    }

    private function formatPrompt(OgiriPrompt $prompt, bool $includeAnswers = false): array
    {
        $data = [
            'id' => $prompt->id,
            'title' => $prompt->title,
            'body' => $prompt->body,
            'image_url' => $prompt->image_url,
            'published_at' => $prompt->published_at,
            'answers_count' => $prompt->answers_count ?? $prompt->answers()->where('is_hidden', false)->count(),
        ];

        if ($includeAnswers) {
            $data['answers'] = $prompt->answers->map(fn ($answer): array => [
                'id' => $answer->id,
                'name' => $answer->name,
                'body' => $answer->body,
                'funny_count' => $answer->funny_count,
                'genius_count' => $answer->genius_count,
                'created_at' => $answer->created_at,
            ])->all();
        }

        return $data;
    }
}
