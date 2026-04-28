<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateArticleRequest extends FormRequest
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
        $currentSlug = (string) $this->route('slug');

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('articles', 'slug')->ignore($currentSlug, 'slug'),
            ],
            'excerpt' => ['nullable', 'string'],
            'body' => ['sometimes', 'required', 'string'],
            'type' => ['nullable', Rule::in(['episode', 'editorial'])],
            'published_at' => ['nullable', 'date'],
            'is_public' => ['sometimes', 'boolean'],
        ];
    }
}
