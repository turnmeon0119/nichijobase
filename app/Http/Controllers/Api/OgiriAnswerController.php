<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOgiriAnswerRequest;
use App\Models\OgiriPrompt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OgiriAnswerController extends Controller
{
    public function store(StoreOgiriAnswerRequest $request, OgiriPrompt $prompt): JsonResponse
    {
        if (! $prompt->is_public || $prompt->published_at === null || $prompt->published_at->isFuture()) {
            abort(404);
        }

        $validated = $request->validated();
        $answer = $prompt->answers()->create([
            ...$validated,
            'created_ip' => $request->ip(),
        ]);

        return response()->json([
            'data' => [
                'id' => $answer->id,
                'ogiri_prompt_id' => $answer->ogiri_prompt_id,
                'name' => $answer->name,
                'body' => $answer->body,
                'funny_count' => $answer->funny_count,
                'genius_count' => $answer->genius_count,
                'created_at' => $answer->created_at,
            ],
        ], 201);
    }

    public function react(Request $request, OgiriPrompt $prompt, int $answer): JsonResponse
    {
        if (! $prompt->is_public || $prompt->published_at === null || $prompt->published_at->isFuture()) {
            abort(404);
        }

        $target = $prompt->answers()->whereKey($answer)->where('is_hidden', false)->firstOrFail();
        $validated = $request->validate([
            'type' => ['required', 'in:funny,genius'],
        ]);
        $column = $validated['type'].'_count';

        $target->increment($column);
        $target->refresh();

        return response()->json([
            'data' => [
                'funny_count' => $target->funny_count,
                'genius_count' => $target->genius_count,
            ],
        ]);
    }
}
