<?php

namespace App\Filament\Admin\Resources\Categories\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkAction;

class CategoriesTable
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
            TextColumn::make('parent.name')
                ->label('Parent Category')
                ->searchable()
                ->sortable(),
            TextColumn::make('creator.name')
                ->label('Created By')
                ->searchable()
                ->sortable(),
            ToggleColumn::make('is_active'),
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
                ->url(fn($record) => route('filament.admin.resources.categories.edit', $record)),
            DeleteAction::make()
                ->label('Delete')
                ->action(fn($record) => $record->delete())
                ->modalHeading('Delete Category')
                ->modalSubheading('Are you sure you want to delete this category?')
                ->modalButton('Delete Category')
                ->successNotificationTitle('Category deleted')
        ];
    }

    public static function getBulkActions(): array
    {
        return [
            BulkAction::make('delete')
                ->label('Delete Selected')
                ->action(fn($records) => $records->each->delete())
                ->modalHeading('Delete Categories')
                ->modalSubheading('Are you sure you want to delete the selected categories?')
                ->modalButton('Delete Categories')
                ->successNotificationTitle('Categories deleted')
        ];
    }
}
