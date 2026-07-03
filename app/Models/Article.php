<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Article extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'type',
        'published_at',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_public' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_public', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', Carbon::now());
    }

    public function pageViews(): HasMany
    {
        return $this->hasMany(PageView::class);
    }

    public function boardThread(): HasOne
    {
        return $this->hasOne(BoardThread::class);
    }
}
