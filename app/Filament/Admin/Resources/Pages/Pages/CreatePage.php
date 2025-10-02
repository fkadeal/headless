<?php

namespace App\Filament\Admin\Resources\Pages\Pages;

use App\Filament\Admin\Resources\Pages\PageResource;
use App\Services\CategoryService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;
    
    protected function handleRecordCreation(array $data): Model
    {
        $pageCategory = CategoryService::getOrCreatePageCategory();
        
        // Ensure the category_id is set to the Page category
        $data['category_id'] = $pageCategory->id;
        
        return static::getModel()::create($data);
    }
}