<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HitokotoPost extends Model
{
    protected $fillable = [
        'name',
        'body',
        'created_ip',
        'is_hidden',
        'reports_count',
        'pow_count',
        'comments_count',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
    ];

    public function comments(): HasMany
    {
        return $this->hasMany(HitokotoComment::class);
    }
}
