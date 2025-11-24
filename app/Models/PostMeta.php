<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Post;

class PostMeta extends Model
{
    protected $fillable = [
        'post_id',
        'meta_key',
        'meta_value',
    ];

    protected $casts = [
        'meta_value' => 'string',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
