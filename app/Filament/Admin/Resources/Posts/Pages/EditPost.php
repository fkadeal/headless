<?php

namespace App\Filament\Admin\Resources\Posts\Pages;

use App\Filament\Admin\Resources\Posts\PostResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // For editing, only set created_by if it's explicitly provided in the form data
        // This prevents accidentally changing the author when other fields are updated
        if (!isset($data['created_by']) || empty($data['created_by'])) {
            // Remove created_by from data to preserve the original author
            unset($data['created_by']);
        }

        // Preserve the post_type to prevent it from being changed during edit
        // The post_type should only change when explicitly creating a new post type
        unset($data['post_type']); // This ensures the original post_type is preserved

        // Extract custom fields data from meta_data if present
        if (isset($data['meta_data']) && is_array($data['meta_data'])) {
            // Store custom fields in session to persist between requests
            session()->put('post_custom_fields_data', $data['meta_data']);
            unset($data['meta_data']); // Remove from main data to prevent conflicts
        }

        return $data;
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        // Update the main record first
        $record->update($data);

        // Retrieve custom fields from session and save them
        $metaData = session()->get('post_custom_fields_data');
        if (is_array($metaData)) {
            foreach ($metaData as $key => $value) {
                $record->setMeta($key, $value);
            }
        }

        // Clear the session data
        session()->forget('post_custom_fields_data');

        return $record;
    }
}
