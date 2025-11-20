<?php

namespace App\Filament\Admin\Resources\CustomPosts\Pages;

use App\Filament\Admin\Resources\CustomPosts\CustomPostResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCustomPost extends CreateRecord
{
    protected static string $resource = CustomPostResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Get the custom post type ID from the query parameter
        $customPostTypeId = request()->query('custom_post_type_id');

        // If no custom post type ID is provided, redirect back with an error
        if (!$customPostTypeId) {
            abort(400, 'Custom post type not specified. Please navigate from the appropriate menu.');
        }

        // Add the custom post type ID to the data
        $data['custom_post_type_id'] = $customPostTypeId;

        return static::getModel()::create($data);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $customPostTypeId = request()->query('custom_post_type_id');

        if (!$customPostTypeId) {
            abort(400, 'Custom post type not specified. Please navigate from the appropriate menu.');
        }

        $data['custom_post_type_id'] = $customPostTypeId;

        return $data;
    }
}
