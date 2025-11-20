<?php

namespace App\Filament\Admin\Resources\CustomPosts\Pages;

use App\Filament\Admin\Resources\CustomPosts\CustomPostResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Models\CustomPost;

class ListCustomPosts extends ListRecords
{
    protected static string $resource = CustomPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        // Check if there's a custom post type filter in the request
        $customPostTypeId = request()->query('custom_post_type_id');

        if ($customPostTypeId) {
            $query->where('custom_post_type_id', $customPostTypeId);
        }

        return $query;
    }
}
