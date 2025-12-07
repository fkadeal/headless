<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Widgets\Widget;
use App\Filament\Admin\Widgets\VotingAnalyticsChart;
use App\Filament\Admin\Widgets\TopVotedPostsChart;
use App\Filament\Admin\Widgets\VotesPerCategory;
use BackedEnum;

class VotingAnalytics extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected string $view = 'filament.admin.pages.voting-analytics';

    protected static ?string $navigationLabel = 'Voting Analytics';

    protected static ?string $title = 'Voting Analytics';

    public function getHeaderWidgets(): array
    {
        return [
            VotingAnalyticsChart::class,
            TopVotedPostsChart::class,
            VotesPerCategory::class,
        ];
    }

    public static function canAccess(): bool
    {
        // Only allow authenticated users to access this page
        return auth()->check();
    }
}
