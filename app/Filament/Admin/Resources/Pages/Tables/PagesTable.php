<?php

namespace App\Filament\Admin\Resources\Pages\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Filament\Actions\{EditAction, DeleteAction};
use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;

class PagesTable
{
    public static function getColumns(): array
    {
        return [
            TextColumn::make('title')
                ->searchable()
                ->sortable()
                ->label('Page Title')
                ->description(fn ($record) => $record->excerpt ? str($record->excerpt)->limit(60) : null),
            
            ImageColumn::make('thumbnail')
                ->label('Thumbnail')
                ->circular()
                ->disk('public')
                ->defaultImageUrl('/storage/thumbnails/default-thumbnail.svg'),
            
            TextColumn::make('slug')
                ->searchable()
                ->sortable()
                ->copyable()
                ->copyMessage('Slug copied')
                ->copyMessageDuration(1500),
            
            TextColumn::make('author.name')
                ->label('Author')
                ->searchable()
                ->sortable(),
            
            ToggleColumn::make('is_published')
                ->label('Published'),
            
            TextColumn::make('published_at')
                ->dateTime()
                ->sortable(),
            
            TextColumn::make('updated_at')
                ->dateTime()
                ->sortable()
                ->label('Last Modified'),
        ];
    }

    public static function getRecordActions(): array
    {
        return [
            EditAction::make()
                ->label('Edit')
                ->color('primary'),
            
            DeleteAction::make()
                ->label('Delete')
                ->modalHeading('Delete Page')
                ->modalButton('Delete')
                ->successNotificationTitle('Page deleted'),
        ];
    }

    public static function getBulkActions(): array
    {
        return [
            BulkAction::make('delete')
                ->label('Delete Selected')
                ->action(fn(Collection $records) => $records->each->delete())
                ->modalHeading('Delete Pages')
                ->modalButton('Delete')
                ->successNotificationTitle('Pages deleted'),
        ];
    }
}