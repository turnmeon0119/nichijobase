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
