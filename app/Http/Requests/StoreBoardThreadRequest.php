<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBoardThreadRequest extends FormRequest
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
            'article_id' => [
                'nullable',
                'integer',
                'exists:articles,id',
                Rule::unique('board_threads', 'article_id'),
            ],
            'title' => ['required', 'string', 'max:120'],
            'name' => ['nullable', 'string', 'max:40'],
            'body' => ['required', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'image_caption' => ['nullable', 'string', 'max:160'],
        ];
    }
}
