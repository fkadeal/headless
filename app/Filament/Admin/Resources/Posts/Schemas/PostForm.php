<?php

namespace App\Filament\Admin\Resources\Posts\Schemas;

use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\DateTimePicker;
use \Filament\Forms\Components\Select;


class PostForm
{
    public static function configure(): array
    {
        return [
            \Filament\Forms\Components\TextInput::make('title')
                ->required()
                ->maxLength(255)
                ->label('Title')
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    // Auto-update slug if it matches old slug
                    $oldSlug = $get('slug');
                    if ($oldSlug !== \Illuminate\Support\Str::slug($get('title'))) {
                        $set('slug', \Illuminate\Support\Str::slug($state));
                    }
                }),

            \Filament\Forms\Components\TextInput::make('slug')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->label('Slug'),

            \Filament\Forms\Components\Textarea::make('excerpt')
                ->maxLength(65535)
                ->label('Excerpt'),



            Select::make('category_id')
                ->relationship('category', 'name')
                ->searchable()
                ->preload()
                ->nullable()
                ->label('Category'),

            Select::make('created_by')
                ->relationship('author', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->label('Author'),

            \Filament\Forms\Components\Toggle::make('is_published')
                ->label('Is Published')
                ->default(false),

            \Filament\Forms\Components\Toggle::make('is_active')
                ->label('Is Active')
                ->default(true),
            TinyEditor::make('content')
                ->profile('full')
                ->columnSpanFull()
                ->fileAttachmentsDisk('public')
                ->fileAttachmentsDirectory('uploads')
                ->required(),

            \Filament\Forms\Components\Repeater::make('images')
                ->relationship()
                ->schema([
                    \Filament\Forms\Components\FileUpload::make('path')
                        ->image()
                        ->disk('public')
                        ->directory('post-images')
                        ->required()
                        ->label('Image Path'),
                    \Filament\Forms\Components\TextInput::make('order')
                        ->numeric()
                        ->default(0)
                        ->label('Order'),
                ])
                ->defaultItems(1)
                ->collapsible()
                ->itemLabel(fn(array $state): ?string => $state['path'] ?? null)
                ->columnSpanFull()
                ->label('Post Images'),
            Select::make('tags')
                ->relationship('tags', 'name')
                ->searchable()
                ->preload()
                ->multiple()
                ->required()
                ->label('Tags'),
            DateTimePicker::make('created_at')
                ->label('Date')
        ];
    }
}
