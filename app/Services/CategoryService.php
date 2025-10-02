<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Str;

class CategoryService
{
    public static function getOrCreatePageCategory(): Category
    {
        $pageCategory = Category::firstWhere('name', 'Page');
        
        if (!$pageCategory) {
            // Get the first user as the creator or create a default one
            $user = User::first();
            if (!$user) {
                $user = User::create([
                    'name' => 'System',
                    'email' => 'system@example.com',
                    'password' => bcrypt('password'),
                ]);
            }
            
            $pageCategory = Category::create([
                'name' => 'Page',
                'slug' => Str::slug('Page'),
                'description' => 'Category for static pages',
                'is_active' => true,
                'created_by' => $user->id,
            ]);
        }
        
        return $pageCategory;
    }
}