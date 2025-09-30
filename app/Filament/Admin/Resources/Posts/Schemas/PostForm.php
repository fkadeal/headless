<?php

namespace App\Filament\Admin\Resources\Posts\Schemas;

use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\RichEditor;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->label('Title'),
                
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->label('Slug'),
                
                Textarea::make('excerpt')
                    ->maxLength(65535)
                    ->label('Excerpt'),
                
                RichEditor::make('content')
                    ->required()
                    ->label('Content'),
                
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->nullable()
                    ->label('Category'),
                
                Select::make('created_by')
                    ->relationship('author', 'name')
                    ->required()
                    ->label('Author'),
                
                Toggle::make('is_published')
                    ->label('Is Published')
                    ->default(false),
                
                Toggle::make('is_active')
                    ->label('Is Active')
                    ->default(true),
            ]);
    }
}
