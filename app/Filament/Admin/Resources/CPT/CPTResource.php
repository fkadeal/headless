<?php

namespace App\Filament\Admin\Resources\CPT;

use App\Filament\Admin\Resources\CPT\Pages\ListCPTs;
use App\Filament\Admin\Resources\CPT\Pages\CreateCPT;
use App\Filament\Admin\Resources\CPT\Pages\EditCPT;
use App\Filament\Admin\Resources\CPT\Schemas\CPTForm;
use App\Filament\Admin\Resources\CPT\Tables\CPTsTable;
use Illuminate\Database\Eloquent\Model;
use App\Models\CustomPostType;
use App\Models\Post;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Panel;

class CPTResource extends Resource
{
    protected static ?string $model = Post::class;
    public static ?string $navigationGroupd = 'Content Management';

    // protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';


    public static function form(Schema $schema): Schema
    {
        return $schema->schema(CPTForm::configure());
    }

    public static function getUrl(
        ?string $name = null,
        array $parameters = [],
        bool $isAbsolute = true,
        ?string $panel = null,
        ?Model $tenant = null,
        bool $shouldGuessMissingParameters = false
    ): string {
        // Ensure post_type is always included
        $parameters['post_type'] =
            $parameters['post_type']
            ?? request()->route('post_type')
            ?? request()->query('post_type')
            ?? 'post';

        return parent::getUrl(
            $name,
            $parameters,
            $isAbsolute,
            $panel,
            $tenant,
            $shouldGuessMissingParameters
        );
    }

    // MUST BE STATIC – no dynamic slugs allowed
    public static function getSlug(?Panel $panel = null): string
    {
        return 'cpt';
    }

    public static function table(Table $table): Table
    {
        return $table->columns(CPTsTable::getColumns());
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $postType = request()->route('post_type'); // FIXED

        if ($postType) {
            $query->where('post_type', $postType);
        }

        return $query;
    }

    public static function getModelLabel(): string
    {
        $postType = request()->route('post_type'); // FIXED

        if ($postType) {
            $cpt = CustomPostType::where('slug', $postType)->first();
            if ($cpt) {
                return $cpt->singular_label ?? $cpt->name;
            }
        }

        return 'Post';
    }

    public static function getPluralModelLabel(): string
    {
        $postType = request()->route('post_type'); // FIXED

        if ($postType) {
            $cpt = CustomPostType::where('slug', $postType)->first();
            if ($cpt) {
                return $cpt->plural_label ?? $cpt->name . 's';
            }
        }

        return 'Posts';
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListCPTs::route('/{post_type}'),
            'create' => CreateCPT::route('/{post_type}/create'),
            'edit'   => EditCPT::route('/{post_type}/{record}/edit'),
        ];
    }
}
