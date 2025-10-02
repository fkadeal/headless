<?php

namespace App\Filament\Admin\Resources\Pages\Pages;

use App\Filament\Admin\Resources\Pages\PageResource;
use App\Services\CategoryService;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;
    
    protected function handleRecordUpdate($record, array $data): Model
    {
        $pageCategory = CategoryService::getOrCreatePageCategory();
        
        // Ensure the category_id is set to the Page category
        $data['category_id'] = $pageCategory->id;
        
        $record->update($data);
        
        return $record;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $pageCategory = CategoryService::getOrCreatePageCategory();
        
        // Ensure category is always set to "Page" category
        $data['category_id'] = $pageCategory->id;
        
        return $data;
    }
}