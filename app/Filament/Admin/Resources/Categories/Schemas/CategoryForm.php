<?php

namespace App\Filament\Admin\Resources\Categories\Schemas;

use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
