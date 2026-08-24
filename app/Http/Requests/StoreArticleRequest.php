<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:articles,slug'],
            'excerpt' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'image_caption' => ['nullable', 'string', 'max:160'],
            'body' => ['required', 'string'],
            'type' => ['nullable', Rule::in(['episode', 'editorial'])],
            'published_at' => ['nullable', 'date'],
            'is_public' => ['sometimes', 'boolean'],
            'save_mode' => ['nullable', Rule::in(['draft', 'publish', 'save'])],
        ];
    }
}
