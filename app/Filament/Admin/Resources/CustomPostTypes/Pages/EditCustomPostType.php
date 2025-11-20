<?php

namespace App\Filament\Admin\Resources\CustomPostTypes\Pages;

use App\Filament\Admin\Resources\CustomPostTypes\CustomPostTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomPostType extends EditRecord
{
    protected static string $resource = CustomPostTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
