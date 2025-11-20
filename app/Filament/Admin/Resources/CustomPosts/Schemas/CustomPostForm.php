<?php

namespace App\Filament\Admin\Resources\CustomPosts\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;

class CustomPostForm
{
    public static function configure(Schema $schema): Schema
    {
        $record = $schema->getRecord();

        $fields = [
            Section::make('Basic Information')
                ->description('Basic information for this post')
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->helperText('Title of the post'),

                    TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->helperText('URL-friendly slug'),

                    Toggle::make('is_published')
                        ->label('Published')
                        ->helperText('Set to publish this post'),
                ])
                ->columns(2),
        ];

        // Add content field if it's not a special type that doesn't need it
        if (!$record || ($record->customPostType ?? null)?->slug !== 'page') {
            $fields[] = Section::make('Content')
                ->schema([
                    RichEditor::make('content')
                        ->label('Content')
                        ->helperText('Main content of the post'),
                ]);
        }

        // Add custom fields based on post type configuration
        // Get custom post type from record if exists, otherwise from query parameter
        $customPostType = null;
        if ($record && $record->customPostType) {
            $customPostType = $record->customPostType;
        } else {
            // When creating a new custom post, get the custom post type from the URL parameter
            $customPostTypeId = request()->query('custom_post_type_id');
            if ($customPostTypeId) {
                $customPostType = \App\Models\Models\CustomPostType::find($customPostTypeId);
            }
        }

        if ($customPostType && $customPostType->config_fields) {
            $fields[] = Section::make('Custom Fields')
                ->description('Custom fields defined for this post type')
                ->schema(self::buildCustomFields($customPostType->config_fields))
                ->columns(2);
        }

        return $schema->components($fields);
    }

    protected static function buildCustomFields(array $configFields): array
    {
        $fields = [];

        foreach ($configFields as $configField) {
            $field = null;

            switch ($configField['type']) {
                case 'text':
                    $field = TextInput::make('custom_fields_data.' . $configField['name'])
                        ->label($configField['label'])
                        ->helperText($configField['label']);
                    break;

                case 'textarea':
                    $field = Textarea::make('custom_fields_data.' . $configField['name'])
                        ->label($configField['label'])
                        ->helperText($configField['label'])
                        ->rows(4);
                    break;

                case 'rich-editor':
                    $field = RichEditor::make('custom_fields_data.' . $configField['name'])
                        ->label($configField['label'])
                        ->helperText($configField['label']);
                    break;

                case 'markdown-editor':
                    $field = MarkdownEditor::make('custom_fields_data.' . $configField['name'])
                        ->label($configField['label'])
                        ->helperText($configField['label']);
                    break;

                case 'select':
                    $options = [];
                    if (isset($configField['options']) && is_array($configField['options'])) {
                        foreach ($configField['options'] as $option) {
                            if (isset($option['value']) && isset($option['label'])) {
                                $options[$option['value']] = $option['label'];
                            }
                        }
                    }

                    $field = Select::make('custom_fields_data.' . $configField['name'])
                        ->label($configField['label'])
                        ->helperText($configField['label'])
                        ->options($options);
                    break;

                case 'checkbox':
                    $field = Checkbox::make('custom_fields_data.' . $configField['name'])
                        ->label($configField['label'])
                        ->helperText($configField['label']);
                    break;

                case 'toggle':
                    $field = Toggle::make('custom_fields_data.' . $configField['name'])
                        ->label($configField['label'])
                        ->helperText($configField['label']);
                    break;

                case 'date':
                    $field = DatePicker::make('custom_fields_data.' . $configField['name'])
                        ->label($configField['label'])
                        ->helperText($configField['label']);
                    break;

                case 'datetime':
                    $field = DateTimePicker::make('custom_fields_data.' . $configField['name'])
                        ->label($configField['label'])
                        ->helperText($configField['label']);
                    break;

                case 'file':
                    $field = FileUpload::make('custom_fields_data.' . $configField['name'])
                        ->label($configField['label'])
                        ->helperText($configField['label'])
                        ->directory('custom-fields')
                        ->preserveFilenames();
                    break;

                case 'image':
                    $field = FileUpload::make('custom_fields_data.' . $configField['name'])
                        ->label($configField['label'])
                        ->helperText($configField['label'])
                        ->image()
                        ->directory('custom-fields-images')
                        ->preserveFilenames();
                    break;
            }

            if ($field) {
                $fields[] = $field;
            }
        }

        return $fields;
    }
}
