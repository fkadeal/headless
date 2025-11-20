<?php

namespace App\Filament\Admin\Resources\CustomPosts\Pages;

use App\Filament\Admin\Resources\CustomPosts\CustomPostResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomPost extends EditRecord
{
    protected static string $resource = CustomPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    public function getTitle(): string
    {
        $customPostType = $this->record->customPostType;
        if ($customPostType) {
            return 'Edit ' . $customPostType->singular_label;
        }

        return parent::getTitle();
    }
}
