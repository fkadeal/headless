<?php

namespace App\Services;

class CurrentCPT
{
    protected const SESSION_KEY = 'current_post_type';

    /**
     * Set the current CPT type for this session/user.
     */
    public static function set(string $postType): void
    {
        session([self::SESSION_KEY => $postType]);
    }

    /**
     * Get the current CPT type.
     */
    public static function get(): string
    {
        return session(self::SESSION_KEY, 'post'); // default 'post'
    }

    /**
     * Forget the current CPT type.
     */
    public static function forget(): void
    {
        session()->forget(self::SESSION_KEY);
    }
}
