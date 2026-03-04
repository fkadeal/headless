<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Post;
use App\Traits\Filterable;

class CustomPostType extends Model
{
    use SoftDeletes, Filterable;

    protected $fillable = [
        'name',
        'slug',
        'singular_label',
        'plural_label',
        'config_fields',
        'standard_fields',
        'enabled',
        'menu_order',
    ];

    protected $searchable = [
        'name',
        'slug',
        'singular_label',
        'plural_label',
    ];

    protected $casts = [
        'config_fields' => 'array',
        'standard_fields' => 'array',
        'enabled' => 'boolean',
        'menu_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class)->where('post_type', $this->slug);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_custom_post_type');
    }
}
