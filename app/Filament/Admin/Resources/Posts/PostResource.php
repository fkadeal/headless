<?php

namespace App\Filament\Admin\Resources\Posts;

use App\Filament\Admin\Resources\Posts\Pages\CreatePost;
use App\Filament\Admin\Resources\Posts\Pages\EditPost;
use App\Filament\Admin\Resources\Posts\Pages\ListPosts;
use App\Filament\Admin\Resources\Posts\Schemas\PostForm;
use App\Filament\Admin\Resources\Posts\Tables\PostsTable;
use App\Models\Post;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema(PostForm::configure());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(PostsTable::getColumns())
            ->actions(PostsTable::getRecordActions())
            ->bulkActions(PostsTable::getBulkActions());
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        // Check if there's a post_type filter in the request
        $postType = request()->query('post_type');

        if ($postType) {
            // Filter by the specified post type
            $query->where('post_type', $postType);
        } else {
            // Default behavior: exclude pages
            $query->whereHas('category', function ($query) {
                $query->where('name', '!=', 'Page');
            });
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getModelLabel(): string
    {
        $postType = request()->query('post_type');

        if ($postType) {
            $customPostType = \App\Models\Models\CustomPostType::where('slug', $postType)->first();
            if ($customPostType) {
                return $customPostType->singular_label ?? $customPostType->name;
            }
        }

        return parent::getModelLabel() ?? 'Post';
    }

    public static function getPluralModelLabel(): string
    {
        $postType = request()->query('post_type');

        if ($postType) {
            $customPostType = \App\Models\Models\CustomPostType::where('slug', $postType)->first();
            if ($customPostType) {
                return $customPostType->plural_label ?? $customPostType->name . 's';
            }
        }

        return parent::getPluralModelLabel() ?? 'Posts';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }
}
