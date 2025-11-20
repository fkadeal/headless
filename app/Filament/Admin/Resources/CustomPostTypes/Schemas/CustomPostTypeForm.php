<?php

namespace App\Filament\Admin\Resources\CustomPostTypes\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class CustomPostTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->description('Basic information about the custom post type')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Human-readable name for this post type (e.g. "Video Post")'),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('URL-friendly slug (e.g. "video")'),

                        TextInput::make('singular_label')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Label for single entries (e.g. "Video")'),

                        TextInput::make('plural_label')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Label for multiple entries (e.g. "Videos")'),

                        Toggle::make('enabled')
                            ->default(true)
                            ->helperText('Enable this post type in the admin panel'),
                    ])
                    ->columns(2),

                Section::make('Custom Fields Configuration')
                    ->description('Define the custom fields for this post type')
                    ->schema([
                        Repeater::make('config_fields')
                            ->label('Custom Fields')
                            ->schema([
                                TextInput::make('label')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Field label'),

                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Unique field name (used in database)'),

                                Select::make('type')
                                    ->options([
                                        'text' => 'Text',
                                        'textarea' => 'Textarea',
                                        'rich-editor' => 'Rich Editor',
                                        'markdown-editor' => 'Markdown Editor',
                                        'select' => 'Select',
                                        'checkbox' => 'Checkbox',
                                        'toggle' => 'Toggle',
                                        'date' => 'Date',
                                        'datetime' => 'Date & Time',
                                        'file' => 'File Upload',
                                        'image' => 'Image Upload',
                                    ])
                                    ->required()
                                    ->helperText('Field type'),

                                Repeater::make('options')
                                    ->label('Field Options')
                                    ->schema([
                                        TextInput::make('label')
                                            ->required()
                                            ->helperText('Option label'),
                                        TextInput::make('value')
                                            ->required()
                                            ->helperText('Option value'),
                                    ])
                                    ->visible(fn ($get) => in_array($get('../../type'), ['select']))
                                    ->helperText('Field options for select fields'),
                            ])
                            ->helperText('Configure custom fields that will appear when creating content for this post type'),
                    ]),
            ]);
    }
}
