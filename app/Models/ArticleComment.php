<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleComment extends Model
{
    protected $fillable = [
        'article_id',
        'name',
        'body',
        'created_ip',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
