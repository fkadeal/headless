<?php

namespace App\Filament\Admin\Resources\Settings\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkAction;
use App\Models\Category;

class SettingsTable
{
    public static function getColumns(): array
    {
        return [
            TextColumn::make('key')
                ->label('Setting Type')
                ->searchable()
                ->sortable(),

            TextColumn::make('value')
                ->label('Configuration')
                ->formatStateUsing(function ($state) {
                    if (is_array($state)) {
                        // Format the array for display
                        $formatted = [];

                        if (isset($state['post_type'])) {
                            $formatted[] = "Post Type: " . ucfirst($state['post_type']);
                        }

                        if (isset($state['category_id'])) {
                            $category = Category::find($state['category_id']);
                            $formatted[] = "Category: " . ($category ? $category->name : 'Unknown');
                        }

                        if (isset($state['max_actions_per_post']) && $state['max_actions_per_post'] > 0) {
                            $formatted[] = "Max per post: " . $state['max_actions_per_post'];
                        }

                        if (isset($state['max_actions_per_user_per_post'])) {
                            $formatted[] = "Max per user per post: " . $state['max_actions_per_user_per_post'];
                        }

                        if (isset($state['max_actions_per_category_per_user']) && $state['max_actions_per_category_per_user'] > 0) {
                            $formatted[] = "Max per category per user: " . $state['max_actions_per_category_per_user'];
                        }

                        if (isset($state['already_voted_message'])) {
                            $formatted[] = "Already voted msg: " . $state['already_voted_message'];
                        }

                        if (isset($state['max_reached_message'])) {
                            $formatted[] = "Max reached msg: " . $state['max_reached_message'];
                        }

                        if (isset($state['success_message'])) {
                            $formatted[] = "Success msg: " . $state['success_message'];
                        }

                        return implode(', ', $formatted);
                    }
                    return $state;
                })
                ->limit(50),

            TextColumn::make('created_at')
                ->label('Created')
                ->dateTime()
                ->sortable(),

            TextColumn::make('updated_at')
                ->label('Updated')
                ->dateTime()
                ->sortable(),
        ];
    }

    public static function getRecordActions(): array
    {
        return [
            Action::make('edit')
                ->label('Edit')
                ->url(fn($record) => route('filament.admin.resources.settings.edit', $record)),
            DeleteAction::make()
                ->label('Delete')
                ->action(fn($record) => $record->delete())
                ->modalHeading('Delete Setting')
                ->modalButton('Delete')
                ->successNotificationTitle('Setting deleted'),
        ];
    }

    public static function getBulkActions(): array
    {
        return [
            BulkAction::make('delete')
                ->label('Delete Selected')
                ->action(fn($records) => $records->each->delete())
                ->modalHeading('Delete Settings')
                ->modalButton('Delete')
                ->successNotificationTitle('Settings deleted'),
        ];
    }
}
