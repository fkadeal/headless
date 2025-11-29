<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SettingsResource\Pages\ManageSettings;
use App\Models\Models\Settings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Category;

class SettingsResource extends Resource
{
    protected static ?string $model = Settings::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $modelLabel = 'Setting';

    protected static ?string $pluralModelLabel = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('General Settings')
                    ->description('General site configuration')
                    ->schema([
                        TextInput::make('key')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Unique identifier for this setting'),
                    ])
                    ->columns(2),

                Section::make('Voting/Liking Settings')
                    ->description('Configuration for voting and liking functionality')
                    ->schema([
                        Select::make('value.category_id')
                            ->label('Category')
                            ->options(Category::pluck('name', 'id')->toArray())
                            ->required()
                            ->helperText('Select the category to configure'),

                        TextInput::make('value.max_votes_per_post')
                            ->label('Maximum votes per post')
                            ->numeric()
                            ->helperText('Maximum number of votes allowed per post')
                            ->default(0),

                        TextInput::make('value.max_votes_per_user_per_post')
                            ->label('Max votes per user per post')
                            ->numeric()
                            ->helperText('Maximum number of times a user can vote on a single post')
                            ->default(1),

                        TextInput::make('value.max_votes_per_category_per_user')
                            ->label('Max votes per user per category')
                            ->numeric()
                            ->helperText('Maximum number of votes a user can cast in a category')
                            ->default(0),

                        TextInput::make('value.vote_expiration_hours')
                            ->label('Vote expiration (hours)')
                            ->numeric()
                            ->helperText('Hours after which a vote expires (0 for no expiration)')
                            ->default(0),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Key')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('value')
                    ->label('Value')
                    ->formatStateUsing(function ($state) {
                        if (is_array($state)) {
                            // Format the array for display
                            $formatted = [];
                            foreach ($state as $key => $value) {
                                if ($key === 'category_id') {
                                    $category = \App\Models\Category::find($value);
                                    $formatted[] = "Category: " . ($category ? $category->name : 'Unknown');
                                } else {
                                    $formatted[] = ucfirst(str_replace('_', ' ', $key)) . ": " . $value;
                                }
                            }
                            return implode(', ', $formatted);
                        }
                        return $state;
                    })
                    ->limit(50),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSettings::route('/'),
        ];
    }
}