<?php

namespace App\Filament\Admin\Resources\Posts\Schemas;

use AmidEsfahani\FilamentTinyEditor\TinyEditor;

class PostForm
{
    public static function configure(): array
    {
        return [
            \Filament\Forms\Components\TextInput::make('title')
                ->required()
                ->maxLength(255)
                ->label('Title'),
            
            \Filament\Forms\Components\TextInput::make('slug')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->label('Slug'),
            
            \Filament\Forms\Components\Textarea::make('excerpt')
                ->maxLength(65535)
                ->label('Excerpt'),
            
            TinyEditor::make('content')
                ->profile('full')
                ->fileAttachmentsDisk('public')
                ->fileAttachmentsDirectory('uploads')
                ->required(),
            
            \Filament\Forms\Components\Select::make('category_id')
                ->relationship('category', 'name')
                ->nullable()
                ->label('Category'),
            
            \Filament\Forms\Components\Select::make('created_by')
                ->relationship('author', 'name')
                ->required()
                ->label('Author'),
            
            \Filament\Forms\Components\Toggle::make('is_published')
                ->label('Is Published')
                ->default(false),
            
            \Filament\Forms\Components\Toggle::make('is_active')
                ->label('Is Active')
                ->default(true),

            \Filament\Forms\Components\FileUpload::make('thumbnail')
                ->image()
                ->disk('public')        
                ->directory('thumbnails')
                ->nullable()
                ->label('Thumbnail')
        ];
    }
}
