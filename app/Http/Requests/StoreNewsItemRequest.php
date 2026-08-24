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
            'body' => ['required', 'string'],
            'is_public' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'save_mode' => ['nullable', Rule::in(['draft', 'publish', 'save'])],
        ];
    }
}
