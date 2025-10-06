<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Str;

class PostSlugTest extends TestCase
{
    use RefreshDatabase;

    public function test_slug_auto_generated_from_title_on_create(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $title = 'Test Post Title';
        $expectedSlug = Str::slug($title);

        $post = Post::create([
            'title' => $title,
            'content' => 'Test content',
            'category_id' => $category->id,
            'created_by' => $user->id,
        ]);

        $this->assertEquals($expectedSlug, $post->slug);
    }

    public function test_slug_not_overwritten_if_explicitly_set(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $title = 'Test Post Title';
        $explicitSlug = 'custom-slug';

        $post = Post::create([
            'title' => $title,
            'slug' => $explicitSlug,
            'content' => 'Test content',
            'category_id' => $category->id,
            'created_by' => $user->id,
        ]);

        $this->assertEquals($explicitSlug, $post->slug);
    }

    public function test_slug_updated_when_title_changes(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $post = Post::create([
            'title' => 'Old Title',
            'content' => 'Test content',
            'category_id' => $category->id,
            'created_by' => $user->id,
        ]);

        $originalSlug = $post->slug;

        $post->update(['title' => 'New Title']);
        $post->refresh();

        $newSlug = Str::slug('New Title');

        $this->assertNotEquals($originalSlug, $post->slug);
        $this->assertEquals($newSlug, $post->slug);
    }
}