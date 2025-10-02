<?php

namespace App\Filament\Admin\Resources\Pages\Schemas;

use App\Filament\Admin\Resources\Posts\Schemas\PostForm;
use App\Services\CategoryService;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;

class PageForm
{
    public static function configure(): array
    {
        $pageCategory = CategoryService::getOrCreatePageCategory();
        
        $originalForm = PostForm::configure();
        
        // Modify the slug field to auto-populate from title
        $modifiedForm = [];
        foreach ($originalForm as $field) {
            $fieldName = method_exists($field, 'getName') ? $field->getName() : null;
            
            if ($fieldName === 'title') {
                // Add title field with live/debounced slug generation
                $modifiedForm[] = $field
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (TextInput $component, $state) {
                        // Update slug field if it's empty or if the title has changed significantly
                        $component->getContainer()->getComponent('slug')?->state(
                            Str::slug($state ?? '')
                        );
                    });
            } elseif ($fieldName === 'slug') {
                // Modify slug field to be auto-generated when title changes
                $modifiedForm[] = $field
                    ->live()
                    ->helperText('Will be auto-generated from title if left empty');
            } elseif ($fieldName === 'category_id') {
                // Replace category_id with hidden field pre-filled with Page category
                $modifiedForm[] = Hidden::make('category_id')
                    ->default($pageCategory->id)
                    ->dehydrated(true);
            } else {
                // Add all other fields as-is
                $modifiedForm[] = $field;
            }
        }
        
        return $modifiedForm;
    }
}