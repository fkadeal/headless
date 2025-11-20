<?php

namespace App\Models\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Models\CustomPost;

class CustomPostType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'singular_label',
        'plural_label',
        'config_fields',
        'enabled',
        'menu_order',
    ];

    protected $casts = [
        'config_fields' => 'array',
        'enabled' => 'boolean',
        'menu_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function customPosts()
    {
        return $this->hasMany(CustomPost::class, 'custom_post_type_id');
    }
}
