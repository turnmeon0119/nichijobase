<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageView extends Model
{
    protected $fillable = [
        'article_id',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
