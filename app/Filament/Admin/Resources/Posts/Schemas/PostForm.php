<?php

namespace App\Filament\Admin\Resources\Posts\Schemas;

use App\Filament\Forms\Components\Ckeditor;
use App\Filament\Forms\Components\TinyMCEEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(): array
    {
        return [
            TextInput::make('title')
                ->required()
                ->maxLength(255)
                ->label('Title')
                ->columnSpanFull()
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

            Textarea::make('excerpt')
                ->maxLength(65535)
                ->label('Excerpt'),



            Select::make('category_id')
                ->relationship('category', 'name')
                ->nullable()
                ->label('Category'),

            Select::make('created_by')
                ->relationship('author', 'name')
                ->required()
                ->label('Author'),

            FileUpload::make('thumbnail')
                ->image()
                ->disk('public')
                ->directory('thumbnails')
                ->imagePreviewHeight(150)
                ->nullable()
                ->label('Thumbnail'),
            Toggle::make('is_published')
                ->label('Is Published')
                ->columns(1 / 2)
                ->default(false),
            Toggle::make('is_active')
                ->label('Is Active')
                ->columns(1 / 2)
                ->default(true),
            // RichEditor::make('content')
            //     ->required()
            //     ->columnSpan(2)
            //     ->label('Content'),
            Ckeditor::make('content')
                ->required()
                ->columnSpanFull()
                ->label('Content'),
            // TinyMCEEditor::make('content')
            //     ->label('Content')
            //     ->required()



        ];
    }
}
