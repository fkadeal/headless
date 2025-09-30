<?php

namespace App\Filament\Admin\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Category Name'),
                
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
                    ->nullable()
                    ->label('Parent Category'),
                
                Select::make('created_by')
                    ->relationship('creator', 'name')
                    ->required()
                    ->label('Created By'),
                
                Toggle::make('is_active')
                    ->label('Is Active')
                    ->default(true),
            ]);
    }
}
