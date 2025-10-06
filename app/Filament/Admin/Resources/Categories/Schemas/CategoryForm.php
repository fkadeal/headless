<?php

namespace App\Filament\Admin\Resources\Categories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;

use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->label('Category Name')
                ->live(onBlur: true)
                ->afterStateUpdated(function (callable $set, ?string $state, ?string $old) {
                    if (($old ?? '') !== Str::slug($state)) {
                        $set('slug', Str::slug($state));
                    }
                }),
            
            TextInput::make('slug')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->label('Slug'),
            
            Textarea::make('description')
                ->maxLength(65535)
                ->label('Description'),
            
            Select::make('parent_id')
                ->relationship('parent', 'name')
                ->label('Parent Category'),
            
            Toggle::make('is_active')
                ->label('Is Active')
                ->default(true),

            FileUpload::make('thumbnail')
                ->image()
                ->nullable()
                ->disk('public')        
                ->directory('thumbnails')
                ->label('Thumbnail'),
        ];
    }
}
