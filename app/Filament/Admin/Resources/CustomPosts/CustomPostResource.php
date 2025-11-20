<?php

namespace App\Filament\Admin\Resources\CustomPosts;

use App\Filament\Admin\Resources\CustomPosts\Pages\CreateCustomPost;
use App\Filament\Admin\Resources\CustomPosts\Pages\EditCustomPost;
use App\Filament\Admin\Resources\CustomPosts\Pages\ListCustomPosts;
use App\Filament\Admin\Resources\CustomPosts\Schemas\CustomPostForm;
use App\Filament\Admin\Resources\CustomPosts\Tables\CustomPostsTable;
use App\Models\Models\CustomPost;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomPostResource extends Resource
{
    protected static ?string $model = CustomPost::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return CustomPostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomPostsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    protected static string|UnitEnum|null $navigationGroup = 'Content Types';

    public static function getBreadcrumb(): string
    {
        // Get the custom post type from the URL parameter or from the record
        $customPostTypeId = request()->query('custom_post_type_id');

        if ($customPostTypeId) {
            $customPostType = \App\Models\Models\CustomPostType::find($customPostTypeId);
            if ($customPostType) {
                return $customPostType->singular_label ?? 'Custom Post';
            }
        }

        return static::$breadcrumb ?? static::$modelLabel ?? 'Custom Post';
    }

    public static function getModelLabel(): string
    {
        // Get the custom post type from the URL parameter or from the record
        $customPostTypeId = request()->query('custom_post_type_id');

        if ($customPostTypeId) {
            $customPostType = \App\Models\Models\CustomPostType::find($customPostTypeId);
            if ($customPostType) {
                return $customPostType->singular_label ?? 'Custom Post';
            }
        }

        return static::$modelLabel ?? 'Custom Post';
    }

    public static function getPluralModelLabel(): string
    {
        // Get the custom post type from the URL parameter or from the record
        $customPostTypeId = request()->query('custom_post_type_id');

        if ($customPostTypeId) {
            $customPostType = \App\Models\Models\CustomPostType::find($customPostTypeId);
            if ($customPostType) {
                return $customPostType->plural_label ?? 'Custom Posts';
            }
        }

        return static::$pluralModelLabel ?? 'Custom Posts';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomPosts::route('/'),
            'create' => CreateCustomPost::route('/create'), // This route will only be accessible via the proper navigation now
            'edit' => EditCustomPost::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
