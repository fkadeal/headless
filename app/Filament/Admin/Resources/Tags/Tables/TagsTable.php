<?php

namespace App\Filament\Admin\Resources\Tags\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkAction;

class TagsTable
{
    public static function getColumns(): array
    {
        return [
            TextColumn::make('name')
                ->searchable()
                ->sortable(),
            TextColumn::make('slug')
                ->searchable()
                ->sortable(),
            TextColumn::make('creator.name')
                ->label('Created By')
                ->searchable()
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
                ->url(fn($record) => route('filament.admin.resources.tags.edit', $record)),
            DeleteAction::make()
                ->label('Delete')
                ->action(fn($record) => $record->delete())
                ->modalHeading('Delete Tag')
                ->modalButton('Delete')
                ->successNotificationTitle('Tag deleted'),
        ];
    }

    public static function getBulkActions(): array
    {
        return [
            BulkAction::make('delete')
                ->label('Delete Selected')
                ->action(fn($records) => $records->each->delete())
                ->modalHeading('Delete Tags')
                ->modalButton('Delete')
                ->successNotificationTitle('Tags deleted'),
        ];
    }
}