<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array', // Cast to array to handle JSON values
    ];

    /**
     * Get a setting value by key
     */
    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Get all settings by key
     */
    public static function getAll($key)
    {
        return static::where('key', $key)->get();
    }

    /**
     * Set a setting value by key
     */
    public static function set($key, $value)
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Set multiple settings for the same key
     */
    public static function add($key, $value)
    {
        return static::create([
            'key' => $key,
            'value' => $value
        ]);
    }

    /**
     * Check if a setting exists
     */
    public static function has($key)
    {
        return static::where('key', $key)->exists();
    }

    /**
     * Remove a setting
     */
    public static function remove($key)
    {
        return static::where('key', $key)->delete();
    }
}
