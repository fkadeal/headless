<?php

namespace App\Filament\Admin\Resources\Pages;

use App\Filament\Admin\Resources\Pages\Pages\CreatePage;
use App\Filament\Admin\Resources\Pages\Pages\EditPage;
use App\Filament\Admin\Resources\Pages\Pages\ListPages;
use App\Filament\Admin\Resources\Pages\Schemas\PageForm;
use App\Filament\Admin\Resources\Pages\Tables\PagesTable;
use App\Models\Post;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PageResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document';

    public static function getNavigationLabel(): string
    {
        return 'Pages';
    }

    public static function getModelLabel(): string
    {
        return 'Page';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Pages';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema(PageForm::configure());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(PagesTable::getColumns())
            ->actions(PagesTable::getRecordActions())
            ->bulkActions(PagesTable::getBulkActions());
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('category', function ($query) {
                $query->where('name', 'Page');
            });
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }
}