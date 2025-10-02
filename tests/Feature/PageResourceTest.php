<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Services\CategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_category_is_created_automatically()
    {
        // Verify that the "Page" category does not exist initially
        $this->assertNull(Category::where('name', 'Page')->first());
        
        // Call the service method to get or create the page category
        $pageCategory = CategoryService::getOrCreatePageCategory();
        
        // Verify that the "Page" category now exists
        $this->assertNotNull($pageCategory);
        $this->assertEquals('Page', $pageCategory->name);
        $this->assertEquals('page', $pageCategory->slug);
    }

    public function test_page_resource_filters_posts_by_category()
    {
        // Create a user for the categories and posts
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        
        // Create the "Page" category
        $pageCategory = CategoryService::getOrCreatePageCategory();
        
        // Create a regular category
        $regularCategory = Category::create([
            'name' => 'News',
            'slug' => 'news',
            'is_active' => true,
            'created_by' => $user->id,
        ]);
        
        // Create posts with different categories
        Post::create([
            'title' => 'Home Page',
            'slug' => 'home-page',
            'category_id' => $pageCategory->id,
            'created_by' => $user->id,
            'is_published' => true,
            'content' => 'Home page content',
        ]);
        
        Post::create([
            'title' => 'About Page',
            'slug' => 'about-page',
            'category_id' => $pageCategory->id,
            'created_by' => $user->id,
            'is_published' => true,
            'content' => 'About page content',
        ]);
        
        Post::create([
            'title' => 'News Post',
            'slug' => 'news-post',
            'category_id' => $regularCategory->id,
            'created_by' => $user->id,
            'is_published' => true,
            'content' => 'News content',
        ]);
        
        // Test the query that the PageResource uses
        $pagePosts = Post::whereHas('category', function ($query) {
                $query->where('name', 'Page');
            })->get();
        
        // Should only return posts with "Page" category
        $this->assertCount(2, $pagePosts);
        foreach ($pagePosts as $post) {
            $this->assertEquals('Page', $post->category->name);
        }
    }
}