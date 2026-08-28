<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HitokotoPost extends Model
{
    protected $fillable = [
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
