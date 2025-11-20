<?php

namespace App\Filament\Admin\Resources\CustomPosts\Pages;

use App\Filament\Admin\Resources\CustomPosts\CustomPostResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redirect;

class CreateCustomPost extends CreateRecord
{
    protected static string $resource = CustomPostResource::class;

    public function mount(): void
    {
        $customPostTypeId = request()->query('custom_post_type_id');

        if (!$customPostTypeId) {
            abort(400, 'Custom post type not specified. Please navigate from the appropriate menu.');
        }

        // Verify the custom post type exists
        $customPostType = \App\Models\Models\CustomPostType::find($customPostTypeId);
        if (!$customPostType) {
            abort(404, 'Custom post type not found.');
        }

        parent::mount();
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Get the custom post type ID from the query parameter
        $customPostTypeId = request()->query('custom_post_type_id');

        // Add the custom post type ID to the data
        $data['custom_post_type_id'] = $customPostTypeId;

        return static::getModel()::create($data);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $customPostTypeId = request()->query('custom_post_type_id');

        $data['custom_post_type_id'] = $customPostTypeId;

        return $data;
    }
}
