<?php

namespace App\Filament\Admin\Resources\Tags\Schemas;

use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Schema;

class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Tag Name'),
                
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->label('Slug'),
                
                Textarea::make('description')
                    ->maxLength(65535)
                    ->label('Description'),
                
                Select::make('created_by')
                    ->relationship('creator', 'name')
                    ->required()
                    ->label('Created By'),
            ]);
    }
}
