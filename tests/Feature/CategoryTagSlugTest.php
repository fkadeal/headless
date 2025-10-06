<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Str;

class CategoryTagSlugTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_slug_auto_generated_from_name_on_create(): void
    {
        $user = User::factory()->create();

        $name = 'Test Category Name';
        $expectedSlug = Str::slug($name);

        $category = Category::create([
            'name' => $name,
            'description' => 'Test description',
            'created_by' => $user->id,
        ]);

        $this->assertEquals($expectedSlug, $category->slug);
    }

    public function test_category_slug_not_overwritten_if_explicitly_set(): void
    {
        $user = User::factory()->create();

        $name = 'Test Category Name';
        $explicitSlug = 'custom-category-slug';

        $category = Category::create([
            'name' => $name,
            'slug' => $explicitSlug,
            'description' => 'Test description',
            'created_by' => $user->id,
        ]);

        $this->assertEquals($explicitSlug, $category->slug);
    }

    public function test_category_slug_updated_when_name_changes(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Old Category Name',
            'description' => 'Test description',
            'created_by' => $user->id,
        ]);

        $originalSlug = $category->slug;

        $category->update(['name' => 'New Category Name']);
        $category->refresh();

        $newSlug = Str::slug('New Category Name');

        $this->assertNotEquals($originalSlug, $category->slug);
        $this->assertEquals($newSlug, $category->slug);
    }

    public function test_tag_slug_auto_generated_from_name_on_create(): void
    {
        $user = User::factory()->create();

        $name = 'Test Tag Name';
        $expectedSlug = Str::slug($name);

        $tag = Tag::create([
            'name' => $name,
            'description' => 'Test description',
            'created_by' => $user->id,
        ]);

        $this->assertEquals($expectedSlug, $tag->slug);
    }

    public function test_tag_slug_not_overwritten_if_explicitly_set(): void
    {
        $user = User::factory()->create();

        $name = 'Test Tag Name';
        $explicitSlug = 'custom-tag-slug';

        $tag = Tag::create([
            'name' => $name,
            'slug' => $explicitSlug,
            'description' => 'Test description',
            'created_by' => $user->id,
        ]);

        $this->assertEquals($explicitSlug, $tag->slug);
    }

    public function test_tag_slug_updated_when_name_changes(): void
    {
        $user = User::factory()->create();

        $tag = Tag::create([
            'name' => 'Old Tag Name',
            'description' => 'Test description',
            'created_by' => $user->id,
        ]);

        $originalSlug = $tag->slug;

        $tag->update(['name' => 'New Tag Name']);
        $tag->refresh();

        $newSlug = Str::slug('New Tag Name');

        $this->assertNotEquals($originalSlug, $tag->slug);
        $this->assertEquals($newSlug, $tag->slug);
    }
}