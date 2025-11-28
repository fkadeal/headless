<?php

namespace App\Filament\Admin\Resources\Categories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

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
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    // Auto-update slug if it matches old slug
                    $oldSlug = $get('slug');
                    if ($oldSlug === \Illuminate\Support\Str::slug($get('name'))) {
                        $set('slug', \Illuminate\Support\Str::slug($state));
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

            \Filament\Forms\Components\FileUpload::make('thumbnail')
                ->image()
                ->disk('public')
                ->directory('thumbnails')
                ->nullable()
                ->label('Thumbnail'),
        ];
    }
}
