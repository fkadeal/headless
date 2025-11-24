<?php

namespace App\Filament\Admin\Resources\CPT\Schemas;

use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use App\Models\Category;
use App\Models\User;
use App\Models\CustomPostType;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
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
use App\Services\CurrentCPT;

class CPTForm
{

    public function mount(): void
    {
        // Resolve once from route or query string
        $postType = request()->route('post_type')
            ?? request()->query('post_type');

        // Store in service
        if (isset($postType)) {
            CurrentCPT::set($postType);
        }
    }

    /**
     * Configure form schema for custom post types
     *
     * @return Section[]
     */
    public static function configure(): array
    {
        $record = self::getRecord();
        $postType = CurrentCPT::get();

        $customPostType = $postType
            ? CustomPostType::where('slug', $postType)->first()
            : null;

        $availableStandardFields = self::getStandardFields();

        $standardFieldsToInclude = $customPostType?->standard_fields ?? [
            'title',
            'slug',
            'content',
            'excerpt',
            'category_id',
            'is_published',
            'created_by',
            'is_active',
            'thumbnail'
        ];

        // Ensure required fields are always included
        foreach (['category_id', 'created_by'] as $required) {
            if (!in_array($required, $standardFieldsToInclude)) {
                $standardFieldsToInclude[] = $required;
            }
        }

        $fields = [];

        // Build sections dynamically
        $sections = [
            'Basic Information' => ['title', 'slug', 'is_published', 'is_active', 'category_id', 'created_by'],
            'Content' => ['content', 'excerpt'],
            'Media' => ['featured_image', 'thumbnail'],
        ];

        foreach ($sections as $title => $sectionFields) {
            $fieldsToInclude = array_filter($sectionFields, fn($f) => in_array($f, $standardFieldsToInclude));
            if ($fieldsToInclude) {
                $sectionSchema = [];
                foreach ($fieldsToInclude as $fieldKey) {
                    $field = $availableStandardFields[$fieldKey]();

                    // Set defaults and hide fields if required but not explicitly included in custom config
                    if ($fieldKey === 'category_id' && (!in_array('category_id', $customPostType->standard_fields ?? []))) {
                        $defaultCategory = Category::first();
                        $field = $field->default($record?->category_id ?? $defaultCategory?->id)->hidden();
                    }

                    if ($fieldKey === 'created_by' && (!in_array('created_by', $customPostType->standard_fields ?? []))) {
                        $field = $field->default($record?->created_by ?? Auth::id())->hidden();
                    }

                    $sectionSchema[] = $field;
                }

                $section = Section::make($title)->schema($sectionSchema);
                if ($title === 'Basic Information') {
                    $section->columns(2);
                }

                $fields[] = $section;
            }
        }

        // Add custom fields
        if ($customPostType?->config_fields) {
            $customFields = self::buildCustomFields($customPostType->config_fields, $record);
            if ($customFields) {
                $fields[] = Section::make('Custom Fields')
                    ->description('Custom fields defined for this post type')
                    ->schema($customFields)
                    ->columns(2);
            }
        }

        return $fields;
    }

    protected static function getRecord(): ?Post
    {
        $recordId = request()->route('record');
        return $recordId ? Post::find($recordId) : null;
    }

    protected static function getStandardFields(): array
    {
        return [
            'title' => fn() => TextInput::make('title')->required()->maxLength(255)->helperText('Title of the post'),
            'slug' => fn() => TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true)->helperText('URL-friendly slug'),
            'content' => fn() => TinyEditor::make('content')->profile('full')->columnSpanFull()->fileAttachmentsDisk('public')->fileAttachmentsDirectory('uploads')->helperText('Main content of the post'),
            'excerpt' => fn() => Textarea::make('excerpt')->helperText('Short description of the post')->rows(4),
            'featured_image' => fn() => FileUpload::make('featured_image')->image()->disk('public')->directory('featured-images')->visibility('public')->helperText('Featured image for the post')->columnSpanFull(),
            'thumbnail' => fn() => FileUpload::make('thumbnail')->image()->disk('public')->directory('thumbnails')->nullable()->visibility('public')->helperText('Thumbnail image for the post')->columnSpanFull(),
            'is_published' => fn() => Toggle::make('is_published')->label('Published')->helperText('Set to publish this post'),
            'category_id' => fn() => Select::make('category_id')->options(Category::pluck('name', 'id'))->nullable()->searchable()->helperText('Select a category for this post'),
            'created_by' => fn() => Select::make('created_by')->options(User::pluck('name', 'id'))->default(Auth::id())->nullable()->searchable()->helperText('Select the author for this post (defaults to current user)'),
            'is_active' => fn() => Toggle::make('is_active')->label('Is Active')->helperText('Set to activate this post'),
        ];
    }

    protected static function buildCustomFields(array $configFields, ?Post $record = null): array
    {
        $fields = [];

        foreach ($configFields as $configField) {
            $name = 'meta_data.' . $configField['name'];
            $label = $configField['label'] ?? ucfirst($configField['name']);
            $field = match ($configField['type']) {
                'text' => TextInput::make($name)->label($label)->helperText($label),
                'textarea' => Textarea::make($name)->label($label)->helperText($label)->rows(4),
                'rich-editor' => RichEditor::make($name)->label($label)->helperText($label),
                'markdown-editor' => MarkdownEditor::make($name)->label($label)->helperText($label),
                'select' => Select::make($name)->label($label)->helperText($label)->options(self::buildOptions($configField['options'] ?? [])),
                'checkbox' => Checkbox::make($name)->label($label)->helperText($label),
                'toggle' => Toggle::make($name)->label($label)->helperText($label),
                'date' => DatePicker::make($name)->label($label)->helperText($label),
                'datetime' => DateTimePicker::make($name)->label($label)->helperText($label),
                'file' => FileUpload::make($name)->label($label)->helperText($label)->directory('custom-fields')->preserveFilenames(),
                'image' => FileUpload::make($name)->label($label)->helperText($label)->image()->directory('custom-fields-images')->preserveFilenames(),
                default => null,
            };

            if ($field) {
                // Pre-populate value if editing
                if ($record?->exists && str_starts_with($name, 'meta_data.')) {
                    $metaKey = substr($name, 10);
                    $metaValue = $record->getMeta($metaKey);
                    if ($metaValue !== null) {
                        $field = $field->default($metaValue);
                    }
                }

                $fields[] = $field;
            }
        }

        return $fields;
    }

    protected static function buildOptions(array $options): array
    {
        $result = [];
        foreach ($options as $opt) {
            if (isset($opt['value'], $opt['label'])) {
                $result[$opt['value']] = $opt['label'];
            }
        }
        return $result;
    }
}
