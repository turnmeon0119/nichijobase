<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HitokotoComment extends Model
{
    protected $fillable = [
        'hitokoto_post_id',
        'name',
        'body',
        'created_ip',
        'is_hidden',
        'reports_count',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
    ];
}
