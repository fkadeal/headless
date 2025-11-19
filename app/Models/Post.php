<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Models\PostMeta;
use App\Models\Models\Voting;
use App\Traits\Filterable;

class Post extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'category_id',
        'created_by',
        'is_published',
        'published_at',
        'featured_image',
        'meta_data',
        'thumbnail',
        'post_type',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_published' => 'boolean',
        'meta_data' => 'array',
        'post_type' => 'string',
    ];

    // Define searchable fields
    protected $searchable = [
        'title',
        'content',
        'excerpt',
        'slug',
        'post_type',
    ];

    protected $attributes = [
        'post_type' => 'post', // Default WordPress-like post type
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tag');
    }

    public function meta(): HasMany
    {
        return $this->hasMany(PostMeta::class, 'post_id');
    }

    /**
     * Get a specific meta value for the post
     */
    public function getMeta($key, $default = null)
    {
        $meta = $this->meta()->where('meta_key', $key)->first();
        
        return $meta ? $meta->meta_value : $default;
    }

    /**
     * Add or update a meta value for the post
     */
    public function setMeta($key, $value)
    {
        return $this->meta()->updateOrCreate(
            ['meta_key' => $key],
            ['meta_value' => $value]
        );
    }

    /**
     * Delete a meta key for the post
     */
    public function deleteMeta($key)
    {
        return $this->meta()->where('meta_key', $key)->delete();
    }

    /**
     * Check if a meta key exists for the post
     */
    public function hasMeta($key)
    {
        return $this->meta()->where('meta_key', $key)->exists();
    }

    /**
     * Get all votes for this post
     */
    public function votes()
    {
        return $this->hasMany(Voting::class, 'post_id');
    }

    /**
     * Check if a specific user has voted on this post
     */
    public function hasUserVoted($userId)
    {
        return $this->votes()->where('user_id', $userId)->exists();
    }

    /**
     * Check if a specific IP address has voted on this post
     */
    public function hasIpVoted($ipAddress)
    {
        return $this->votes()->where('ip_address', $ipAddress)->exists();
    }
}