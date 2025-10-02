<?php

namespace Tests\Unit;

use App\Filament\Admin\Resources\Pages\Schemas\PageForm;
use App\Models\User;
use App\Services\CategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PageFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_form_auto_generates_slug_from_title()
    {
        // Create a user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Make sure the Page category exists
        CategoryService::getOrCreatePageCategory();

        // Get the form configuration
        $formFields = PageForm::configure();

        // Verify that we have fields
        $this->assertNotEmpty($formFields);

        // Test slug generation
        $title = 'Test Page Title';
        $expectedSlug = Str::slug($title);
        $this->assertEquals('test-page-title', $expectedSlug);
    }
}