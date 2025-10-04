<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Filterable;

class Category extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'created_by',
        'is_active',
        'thumbnail',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Define searchable fields
    protected $searchable = [
        'name',
        'slug',
        'description',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted()
{
    static::creating(function ($category) {
        if (empty($category->created_by)) {
            $category->created_by = auth()->id();
        }
    });
}
}