<?php

namespace App\Filament\Admin\Resources\Tags\Schemas;

namespace App\Filament\Admin\Resources\Tags\Schemas;

class TagForm
{
    public static function configure(): array
    {
        return [
            \Filament\Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->label('Tag Name'),
            
            \Filament\Forms\Components\TextInput::make('slug')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->label('Slug'),
            
            \Filament\Forms\Components\Textarea::make('description')
                ->maxLength(65535)
                ->label('Description'),
            
            \Filament\Forms\Components\Select::make('created_by')
                ->relationship('creator', 'name')
                ->required()
                ->label('Created By'),
        ];
    }
}
