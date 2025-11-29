<?php

namespace App\Filament\Admin\Resources\Settings\Schemas;

use App\Models\Category;
use App\Models\CustomPostType;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

class SettingForm
{
    public static function configure(): array
    {
        return [
            Section::make('Setting Configuration')
                ->description('Configure the key for this setting')
                ->schema([
                    Select::make('key')
                        ->required()
                        ->options([
                            'voting_rules' => 'Voting Rules',
                            'liking_rules' => 'Liking Rules',
                            'system_config' => 'System Configuration',
                            'messages' => 'Response Messages',
                        ])
                        ->helperText('Select the type of setting'),
                ])
                ->columns(2),

            Section::make('Voting/Liking Rules')
                ->description('Configuration for voting and liking functionality by post type and category')
                ->schema([
                    Select::make('value.post_type')
                        ->label('Post Type')
                        ->options(CustomPostType::pluck('singular_label', 'slug')->toArray())
                        ->helperText('Select the post type to configure (leave empty for all post types)')
                        ->nullable(),

                    Select::make('value.category_id')
                        ->label('Category')
                        ->options(Category::pluck('name', 'id')->toArray())
                        ->nullable()
                        ->helperText('Select the category to configure (leave empty for all categories)'),

                    TextInput::make('value.max_actions_per_post')
                        ->label('Max actions per post')
                        ->numeric()
                        ->helperText('Maximum number of actions (votes/likes) allowed per post (0 for unlimited)')
                        ->default(0),

                    TextInput::make('value.max_actions_per_user_per_post')
                        ->label('Max actions per user per post')
                        ->numeric()
                        ->helperText('Maximum number of times a user can act on a single post')
                        ->default(1),

                    TextInput::make('value.max_actions_per_category_per_user')
                        ->label('Max actions per user per category')
                        ->numeric()
                        ->helperText('Maximum number of actions a user can perform in a category (0 for unlimited)')
                        ->default(0),

                    TextInput::make('value.action_expiration_hours')
                        ->label('Action expiration (hours)')
                        ->numeric()
                        ->helperText('Hours after which an action expires (0 for no expiration)')
                        ->default(0),
                ])
                ->columns(2)
                ->visible(fn($get) => in_array($get('key'), ['voting_rules', 'liking_rules'])),

            Section::make('Response Messages')
                ->description('Customize response messages for different scenarios')
                ->schema([
                    TextInput::make('value.already_voted_message')
                        ->label('Already voted/liked message')
                        ->helperText('Message shown when user has already voted/liked')
                        ->maxLength(255)
                        ->default('You have already voted on this post.'),

                    TextInput::make('value.max_reached_message')
                        ->label('Max actions reached message')
                        ->helperText('Message shown when user has reached the maximum allowed actions')
                        ->maxLength(255)
                        ->default('You have reached the maximum allowed actions.'),

                    TextInput::make('value.success_message')
                        ->label('Success message')
                        ->helperText('Message shown when action is successful')
                        ->maxLength(255)
                        ->default('Action recorded successfully.'),
                ])
                ->columns(1)
                ->visible(fn($get) => $get('key') === 'messages'),
        ];
    }
}
