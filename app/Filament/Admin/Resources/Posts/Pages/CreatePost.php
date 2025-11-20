<?php

namespace App\Filament\Admin\Resources\Posts\Pages;

use App\Filament\Admin\Resources\Posts\PostResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Check if there's a post_type parameter in the URL and ensure it's properly set
        $postTypeFromUrl = request()->query('post_type');
        if ($postTypeFromUrl) {
            $data['post_type'] = $postTypeFromUrl;
        }
        // If post_type is still not set, default to 'post'
        else {
            $data['post_type'] = 'post';
        }

        // If created_by is not set, default to the currently logged-in user
        if (!isset($data['created_by']) || empty($data['created_by'])) {
            $data['created_by'] = auth()->id();
        }

        // If category_id is not set, default to a default category (e.g., the first category)
        if (!isset($data['category_id']) || empty($data['category_id'])) {
            // Try to get a default category, or create a 'General' category if none exists
            $defaultCategory = \App\Models\Category::first();
            if ($defaultCategory) {
                $data['category_id'] = $defaultCategory->id;
            } else {
                // Create a default category if no categories exist
                $defaultCategory = \App\Models\Category::create([
                    'name' => 'General',
                    'slug' => 'general',
                    'description' => 'Default category for posts',
                ]);
                $data['category_id'] = $defaultCategory->id;
            }
        }

        // Extract custom fields data from meta_data if present
        if (isset($data['meta_data']) && is_array($data['meta_data'])) {
            // Store custom fields in session to persist between requests
            session()->put('post_custom_fields_data', $data['meta_data']);
            unset($data['meta_data']); // Remove from main data to prevent conflicts
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $post = static::getModel()::create($data);

        // Retrieve custom fields from session and save them
        $metaData = session()->get('post_custom_fields_data');
        if (is_array($metaData)) {
            foreach ($metaData as $key => $value) {
                $post->setMeta($key, $value);
            }
        }

        // Clear the session data
        session()->forget('post_custom_fields_data');

        return $post;
    }
}
