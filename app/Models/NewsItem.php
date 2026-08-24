<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsItem extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'body',
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

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_public', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(NewsItemBlock::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
