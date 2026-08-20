<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OgiriAnswer extends Model
{
    protected $fillable = [
        'ogiri_prompt_id',
        'name',
        'body',
        'created_ip',
        'is_hidden',
        'reports_count',
        'funny_count',
        'genius_count',
    ];

    public function prompt(): BelongsTo
    {
        return $this->belongsTo(OgiriPrompt::class, 'ogiri_prompt_id');
    }
}
