<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoardPost extends Model
{
    protected $fillable = [
        'board_thread_id',
        'name',
        'body',
        'created_ip',
        'is_hidden',
        'reports_count',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(BoardThread::class, 'board_thread_id');
    }
}
