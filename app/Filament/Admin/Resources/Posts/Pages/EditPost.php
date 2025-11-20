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
        // Extract custom fields data from meta_data if present
        if (isset($data['meta_data']) && is_array($data['meta_data'])) {
            // Store custom fields in session to persist between requests
            session()->put('post_custom_fields_data', $data['meta_data']);
            unset($data['meta_data']); // Remove from main data to prevent conflicts
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
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
