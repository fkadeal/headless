<?php

namespace App\Filament\Admin\Resources\Posts\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkAction;

class PostsTable
{
    public static function getColumns(): array
    {
        return [
            TextColumn::make('title')
                ->searchable()
                ->sortable(),
            
            ImageColumn::make('thumbnail')
                ->label('Thumbnail'),
            TextColumn::make('slug')
                ->searchable()
                ->sortable(),
            TextColumn::make('category.name')
                ->label('Category')
                ->searchable()
                ->sortable(),
            TextColumn::make('author.name')
                ->label('Author')
                ->searchable()
                ->sortable(),
            ToggleColumn::make('is_published'),
            TextColumn::make('published_at')
                ->dateTime()
                ->sortable(),
            TextColumn::make('created_at')
                ->dateTime()
                ->sortable(),
        ];
    }

    public static function getRecordActions(): array
    {
        return [
            Action::make('edit')
                ->label('Edit')
                ->url(fn($record) => route('filament.admin.resources.posts.edit', $record)),
            DeleteAction::make()
                ->label('Delete')
                ->action(fn($record) => $record->delete())
                ->modalHeading('Delete Post')
                ->modalButton('Delete')
                ->successNotificationTitle('Post deleted'),
        ];
    }

    public static function getBulkActions(): array
    {
        return [
            BulkAction::make('delete')
                ->label('Delete Selected')
                ->action(fn($records) => $records->each->delete())
                ->modalHeading('Delete Posts')
                ->modalButton('Delete')
                ->successNotificationTitle('Posts deleted'),
        ];
    }
}
