<?php

namespace App\Filament\Admin\Resources\CustomPostTypes\Pages;

use App\Filament\Admin\Resources\CustomPostTypes\CustomPostTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomPostTypes extends ListRecords
{
    protected static string $resource = CustomPostTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
