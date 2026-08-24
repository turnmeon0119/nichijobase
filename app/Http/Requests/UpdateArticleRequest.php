<?php

namespace App\Http\Requests;

use App\Models\Article;
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
        $article = $this->route('article');
        $currentSlug = (string) $this->route('slug');
        $uniqueSlug = Rule::unique('articles', 'slug');

        if ($article instanceof Article) {
            $uniqueSlug->ignore($article->id);
        } elseif ($currentSlug !== '') {
            $uniqueSlug->ignore($currentSlug, 'slug');
        }

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'alpha_dash',
                $uniqueSlug,
            ],
            'excerpt' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'image_caption' => ['nullable', 'string', 'max:160'],
            'body' => ['nullable', 'string'],
            'blocks' => ['nullable', 'array'],
            'blocks.*.id' => ['nullable', 'integer'],
            'blocks.*.type' => ['required_with:blocks', Rule::in(['text', 'image'])],
            'blocks.*.body' => ['nullable', 'string'],
            'blocks.*.image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'blocks.*.image_caption' => ['nullable', 'string', 'max:160'],
            'type' => ['nullable', Rule::in(['episode', 'editorial'])],
            'published_at' => ['nullable', 'date'],
            'is_public' => ['sometimes', 'boolean'],
            'save_mode' => ['nullable', Rule::in(['draft', 'publish', 'save'])],
        ];
    }
}
