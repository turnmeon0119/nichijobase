<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNewsItemRequest extends FormRequest
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
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:news_items,slug'],
            'body' => ['required_without:blocks', 'nullable', 'string'],
            'blocks' => ['nullable', 'array'],
            'blocks.*.id' => ['nullable', 'integer'],
            'blocks.*.type' => ['required_with:blocks', Rule::in(['text', 'image'])],
            'blocks.*.body' => ['nullable', 'string'],
            'blocks.*.image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'blocks.*.image_caption' => ['nullable', 'string', 'max:160'],
            'is_public' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'save_mode' => ['nullable', Rule::in(['draft', 'publish', 'save'])],
        ];
    }
}
