<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Services\CategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageResourceFullTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_creation_and_listing()
    {
        // Create a user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create the "Page" category
        $pageCategory = CategoryService::getOrCreatePageCategory();

        // Create a page (post with Page category)
        $page = Post::create([
            'title' => 'Home Page',
            'slug' => 'home-page',
            'category_id' => $pageCategory->id,
            'created_by' => $user->id,
            'is_published' => true,
            'content' => 'Home page content',
        ]);

        // Test the PageResource query (what gets shown in the pages list)
        $pagePosts = Post::whereHas('category', function ($query) {
                $query->where('name', 'Page');
            })->get();
        
        // Should return our page
        $this->assertCount(1, $pagePosts);
        $this->assertEquals($page->id, $pagePosts->first()->id);
        
        // Verify the page has correct category
        $this->assertEquals('Page', $pagePosts->first()->category->name);
    }

    public function test_create_page_through_page_resource()
    {
        // Create a user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Test creating a page via the service method
        $pageCategory = CategoryService::getOrCreatePageCategory();

        // Create a page using the same approach as the PageResource
        $page = Post::create([
            'title' => 'About Page',
            'slug' => 'about-page',
            'category_id' => $pageCategory->id,
            'created_by' => $user->id,
            'is_published' => true,
            'content' => 'About page content',
        ]);

        // Verify the page exists and has the correct category
        $this->assertEquals('Page', $page->category->name);
        $this->assertEquals('about-page', $page->slug);
        
        // Check that it appears in the Pages list query
        $pagesList = Post::whereHas('category', function ($query) {
                $query->where('name', 'Page');
            })->get();
            
        $this->assertCount(1, $pagesList);
        $this->assertEquals('About Page', $pagesList->first()->title);
    }
}