<?php

namespace App\Filament\Admin\Resources\Tags\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Illuminate\Support\Str;

class TagForm
{
    public static function configure(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->label('Tag Name')
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
            
            Select::make('created_by')
                ->relationship('creator', 'name')
                ->required()
                ->label('Created By'),
        ];
    }
}
