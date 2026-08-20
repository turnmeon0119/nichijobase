<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OgiriPrompt extends Model
{
    protected $fillable = [
        'title',
        'body',
        'image_url',
        'image_public_id',
        'is_public',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function answers(): HasMany
    {
        return $this->hasMany(OgiriAnswer::class)->orderByDesc('id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_public', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
