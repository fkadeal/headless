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
        // Check if there's a post_type parameter in the URL
        $postType = request()->query('post_type');
        if ($postType) {
            $data['post_type'] = $postType;
        }

        // If created_by is not set, default to the currently logged-in user
        if (!isset($data['created_by']) || empty($data['created_by'])) {
            $data['created_by'] = auth()->id();
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
