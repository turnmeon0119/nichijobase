<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BoardThread extends Model
{
    protected $fillable = [
        'article_id',
        'title',
        'name',
        'body',
        'image_url',
        'image_public_id',
        'image_caption',
        'created_ip',
        'is_hidden',
        'reports_count',
        'empathy_count',
        'perspective_count',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(BoardPost::class)->orderBy('id');
    }
}
