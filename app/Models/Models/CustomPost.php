<?php

namespace App\Models\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Models\CustomPostType;
use App\Models\User;

class CustomPost extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'category_id',
        'featured_image',
        'thumbnail',
        'meta_data',
        'custom_fields_data',
        'custom_post_type_id',
        'created_by',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'meta_data' => 'array',
        'custom_fields_data' => 'array',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function customPostType()
    {
        return $this->belongsTo(CustomPostType::class, 'custom_post_type_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class);
    }
}
