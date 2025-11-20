<?php

namespace App\Filament\Admin\Resources\Posts\Schemas;

use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use App\Models\Category;
use App\Models\User;
use App\Models\Models\CustomPostType;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;

class PostForm
{
    public static function configure(): array
    {
        // Get the post type from request parameter or from the record
        $postType = request()->query('post_type');

        // Get the record if we're editing - check if we're in edit mode
        $route = request()->route();
        $record = null;

        if ($route) {
            $routeParameters = $route->parameters();
            if (isset($routeParameters['record'])) {
                $record = $routeParameters['record'];

                // If record is still a string (ID), try to find the model
                if (is_string($record) || is_numeric($record)) {
                    $record = \App\Models\Post::find($record);
                }
            }
        }

        // If no post type from query, try to get it from the record
        if (!$postType && $record && $record->post_type) {
            $postType = $record->post_type;
        }

        // Get the custom post type configuration if applicable
        $customPostType = null;
        if ($postType) {
            $customPostType = CustomPostType::where('slug', $postType)->first();
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
                return TinyEditor::make('content')
                    ->profile('full')
                    ->columnSpanFull()
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('uploads')
                    ->required()
                    ->helperText('Main content of the post');
            },
            'excerpt' => function() {
                return Textarea::make('excerpt')
                    ->helperText('Short description of the post')
                    ->maxLength(65535);
            },
            'featured_image' => function() {
                return FileUpload::make('featured_image')
                    ->image()
                    ->disk('public')
                    ->directory('featured-images')
                    ->visibility('public')
                    ->helperText('Featured image for the post')
                    ->columnSpanFull();
            },
            'thumbnail' => function() {
                return FileUpload::make('thumbnail')
                    ->image()
                    ->disk('public')
                    ->directory('thumbnails')
                    ->nullable()
                    ->visibility('public')
                    ->helperText('Thumbnail image for the post')
                    ->columnSpanFull();
            },
            'is_published' => function() {
                return Toggle::make('is_published')
                    ->label('Published')
                    ->helperText('Set to publish this post');
            },
            'category_id' => function() {
                return Select::make('category_id')
                    ->options(Category::pluck('name', 'id'))
                    ->nullable()
                    ->searchable()
                    ->helperText('Select a category for this post');
            },
            'created_by' => function() {
                return Select::make('created_by')
                    ->options(User::pluck('name', 'id'))
                    ->default(auth()->id()) // Default to currently logged-in user
                    ->nullable() // Allow null values to be stored if needed
                    ->searchable()
                    ->helperText('Select the author for this post (defaults to current user)');
            },
            'is_active' => function() {
                return Toggle::make('is_active')
                    ->label('Is Active')
                    ->helperText('Set to activate this post');
            },
        ];

        $fields = [];

        // Determine which standard fields to include based on custom post type config
        if ($customPostType && $customPostType->standard_fields) {
            $standardFieldsToInclude = $customPostType->standard_fields;
        } else {
            // Default standard fields for regular posts
            $standardFieldsToInclude = ['title', 'slug', 'content', 'excerpt', 'category_id', 'is_published', 'created_by', 'is_active', 'thumbnail'];
        }

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
        if (in_array('is_active', $standardFieldsToInclude)) {
            $basicFields[] = $availableStandardFields['is_active']();
        }
        if (in_array('category_id', $standardFieldsToInclude)) {
            $basicFields[] = $availableStandardFields['category_id']();
        }
        if (in_array('created_by', $standardFieldsToInclude)) {
            $basicFields[] = $availableStandardFields['created_by']();
        }

        if (!empty($basicFields)) {
            $fields[] = Section::make('Basic Information')
                ->description('Basic information for this post')
                ->schema($basicFields)
                ->columns(2);
        }

        if (in_array('content', $standardFieldsToInclude)) {
            $contentFields[] = $availableStandardFields['content']();
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
            $sectionFields = self::buildCustomFields($customPostType->config_fields);

            // If we're editing a record, populate custom field values from post meta
            if ($record && $record->exists) {
                foreach ($sectionFields as $i => $field) {
                    $fieldName = $field->getName();
                    // Extract the meta key from the field name (meta_data.custom_field_name)
                    if (str_starts_with($fieldName, 'meta_data.')) {
                        $metaKey = substr($fieldName, 10); // Remove 'meta_data.' prefix
                        $metaValue = $record->getMeta($metaKey);
                        if ($metaValue !== null) {
                            $sectionFields[$i] = $field->default($metaValue);
                        }
                    }
                }
            }

            $fields[] = Section::make('Custom Fields')
                ->description('Custom fields defined for this post type')
                ->schema($sectionFields)
                ->columns(2);
        }

        return $fields;
    }

    protected static function buildCustomFields(array $configFields): array
    {
        $fields = [];

        foreach ($configFields as $configField) {
            $field = null;

            switch ($configField['type']) {
                case 'text':
                    $field = TextInput::make('meta_data.' . $configField['name'])
                        ->label($configField['label'])
                        ->helperText($configField['label']);
                    break;

                case 'textarea':
                    $field = Textarea::make('meta_data.' . $configField['name'])
                        ->label($configField['label'])
                        ->helperText($configField['label'])
                        ->rows(4);
                    break;

                case 'rich-editor':
                    $field = RichEditor::make('meta_data.' . $configField['name'])
                        ->label($configField['label'])
                        ->helperText($configField['label']);
                    break;

                case 'markdown-editor':
                    $field = MarkdownEditor::make('meta_data.' . $configField['name'])
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

                    $field = Select::make('meta_data.' . $configField['name'])
                        ->label($configField['label'])
                        ->helperText($configField['label'])
                        ->options($options);
                    break;

                case 'checkbox':
                    $field = Checkbox::make('meta_data.' . $configField['name'])
                        ->label($configField['label'])
                        ->helperText($configField['label']);
                    break;

                case 'toggle':
                    $field = Toggle::make('meta_data.' . $configField['name'])
                        ->label($configField['label'])
                        ->helperText($configField['label']);
                    break;

                case 'date':
                    $field = DatePicker::make('meta_data.' . $configField['name'])
                        ->label($configField['label'])
                        ->helperText($configField['label']);
                    break;

                case 'datetime':
                    $field = DateTimePicker::make('meta_data.' . $configField['name'])
                        ->label($configField['label'])
                        ->helperText($configField['label']);
                    break;

                case 'file':
                    $field = FileUpload::make('meta_data.' . $configField['name'])
                        ->label($configField['label'])
                        ->helperText($configField['label'])
                        ->directory('custom-fields')
                        ->preserveFilenames();
                    break;

                case 'image':
                    $field = FileUpload::make('meta_data.' . $configField['name'])
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
