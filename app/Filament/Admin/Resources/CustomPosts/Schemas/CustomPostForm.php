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

        $fields = [];

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

        // Define standard fields that can be included
        $availableStandardFields = [
            'title' => function() {
                return TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Title of the post');
            },
            'slug' => function() {
                return TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('URL-friendly slug');
            },
            'content' => function() {
                return RichEditor::make('content')
                    ->label('Content')
                    ->helperText('Main content of the post');
            },
            'excerpt' => function() {
                return Textarea::make('excerpt')
                    ->helperText('Short description of the post')
                    ->maxLength(500);
            },
            'featured_image' => function() {
                return FileUpload::make('featured_image')
                    ->image()
                    ->directory('featured-images')
                    ->visibility('public')
                    ->helperText('Featured image for the post');
            },
            'thumbnail' => function() {
                return FileUpload::make('thumbnail')
                    ->image()
                    ->directory('thumbnails')
                    ->visibility('public')
                    ->helperText('Thumbnail image for the post');
            },
            'is_published' => function() {
                return Toggle::make('is_published')
                    ->label('Published')
                    ->helperText('Set to publish this post');
            },
            'category_id' => function() {
                return Select::make('category_id')
                    ->label('Category')
                    ->options(\App\Models\Category::pluck('name', 'id')->toArray())
                    ->searchable()
                    ->helperText('Select a category for this post');
            },
        ];

        // Add selected standard fields based on post type configuration
        $standardFieldsToInclude = $customPostType?->standard_fields ?? ['title', 'slug', 'content', 'is_published'];

        // Group fields by section based on selection
        $basicFields = [];
        $contentFields = [];
        $mediaFields = [];

        if (in_array('title', $standardFieldsToInclude)) {
            $basicFields[] = $availableStandardFields['title']();
        }
        if (in_array('slug', $standardFieldsToInclude)) {
            $basicFields[] = $availableStandardFields['slug']();
        }
        if (in_array('is_published', $standardFieldsToInclude)) {
            $basicFields[] = $availableStandardFields['is_published']();
        }
        if (in_array('category_id', $standardFieldsToInclude)) {
            $basicFields[] = $availableStandardFields['category_id']();
        }

        if (!empty($basicFields)) {
            $fields[] = Section::make('Basic Information')
                ->description('Basic information for this post')
                ->schema($basicFields)
                ->columns(2);
        }

        if (in_array('content', $standardFieldsToInclude)) {
            // Add content field if it's not a special type that doesn't need it
            if (!$record || ($record->customPostType ?? null)?->slug !== 'page') {
                $contentFields[] = $availableStandardFields['content']();
            }
        }

        if (in_array('excerpt', $standardFieldsToInclude)) {
            $contentFields[] = $availableStandardFields['excerpt']();
        }

        if (!empty($contentFields)) {
            $fields[] = Section::make('Content')
                ->schema($contentFields);
        }

        if (in_array('featured_image', $standardFieldsToInclude) || in_array('thumbnail', $standardFieldsToInclude)) {
            if (in_array('featured_image', $standardFieldsToInclude)) {
                $mediaFields[] = $availableStandardFields['featured_image']();
            }

            if (in_array('thumbnail', $standardFieldsToInclude)) {
                $mediaFields[] = $availableStandardFields['thumbnail']();
            }

            if (!empty($mediaFields)) {
                $fields[] = Section::make('Media')
                    ->schema($mediaFields);
            }
        }

        // Add custom fields based on post type configuration
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
