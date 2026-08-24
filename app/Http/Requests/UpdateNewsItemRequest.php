<?php

namespace App\Http\Requests;

use App\Models\NewsItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNewsItemRequest extends FormRequest
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
        $newsItem = $this->route('newsItem');
        $uniqueSlug = Rule::unique('news_items', 'slug');

        if ($newsItem instanceof NewsItem) {
            $uniqueSlug->ignore($newsItem->id);
        }

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', $uniqueSlug],
            'body' => ['required', 'string'],
            'is_public' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'save_mode' => ['nullable', Rule::in(['draft', 'publish', 'save'])],
        ];
    }
}
