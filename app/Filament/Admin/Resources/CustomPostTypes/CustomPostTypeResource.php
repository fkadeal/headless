<?php

namespace App\Filament\Admin\Resources\CustomPostTypes;

use App\Filament\Admin\Resources\CustomPostTypes\Pages\CreateCustomPostType;
use App\Filament\Admin\Resources\CustomPostTypes\Pages\EditCustomPostType;
use App\Filament\Admin\Resources\CustomPostTypes\Pages\ListCustomPostTypes;
use App\Filament\Admin\Resources\CustomPostTypes\Schemas\CustomPostTypeForm;
use App\Filament\Admin\Resources\CustomPostTypes\Tables\CustomPostTypesTable;
use App\Models\Models\CustomPostType;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomPostTypeResource extends Resource
{
    protected static ?string $model = CustomPostType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Custom Post Types';

    protected static ?string $pluralModelLabel = 'Custom Post Types';

    public static function form(Schema $schema): Schema
    {
        return CustomPostTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomPostTypesTable::configure($table);
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
            'index' => ListCustomPostTypes::route('/'),
            'create' => CreateCustomPostType::route('/create'),
            'edit' => EditCustomPostType::route('/{record}/edit'),
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
