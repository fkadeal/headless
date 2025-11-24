<?php

namespace App\Filament\Admin\Resources\CPT\Tables;

use App\Filament\Admin\Resources\CPT\CPTResource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkAction;

class CPTsTable
{
    public static function getColumns(): array
    {
        return [
            TextColumn::make('title')
                ->searchable()
                ->sortable(),

            ImageColumn::make('thumbnail')
                ->label('Thumbnail')
                ->circular()
                ->disk('public'),
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

    // public static function getRecordActions(): array
    // {
    //     return [
    //         // Action::make('edit')
    //         //     ->label('Edit')
    //         //     //     ->url(fn($record) => $record->getEditUrl()),
    //         //     ->url(fn($record) => CPTResource::getUrl('edit', [
    //         //         'record' => $record,
    //         //         'post_type' => $record->post_type, // <-- MUST pass post_type
    //         //     ])),
    //         Action::make('edit')
    //             ->label('Edit')
    //             ->url(fn($record) => route(
    //                 'filament.admin.resources.c-p-t.post.edit', // use the "base" resource route
    //                 [
    //                     'post_type' => $record->post_type,
    //                     'record' => $record->id,
    //                 ]
    //             )),
    //         DeleteAction::make()
    //             ->label('Delete')
    //             ->action(fn($record) => $record->delete())
    //             ->modalHeading('Delete Post')
    //             ->modalButton('Delete')
    //             ->successNotificationTitle('Post deleted'),
    //     ];
    // }

    // public static function getBulkActions(): array
    // {
    //     return [
    //         BulkAction::make('delete')
    //             ->label('Delete Selected')
    //             ->action(fn($records) => $records->each->delete())
    //             ->modalHeading('Delete Posts')
    //             ->modalButton('Delete')
    //             ->successNotificationTitle('Posts deleted'),
    // ];
    // }
}
