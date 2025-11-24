<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Post;
use App\Models\Category;
use App\Models\User;
use App\Models\Voting;

class CustomStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Posts', Post::count())
                ->description('All posts in the system')
                ->color('primary'),

            Stat::make('Total Categories', Category::count())
                ->description('All categories in the system')
                ->color('success'),

            Stat::make('Total Users', User::count())
                ->description('All registered users')
                ->color('warning'),

            Stat::make('Total Votes', Voting::count())
                ->description('All votes casted')
                ->color('info'),

            Stat::make('Posts with Votes', Post::whereHas('votes')->count())
                ->description('Posts that have received at least one vote')
                ->color('success'),

            Stat::make('Active Voters', Voting::select('user_id')->distinct()->whereNotNull('user_id')->count())
                ->description('Unique logged-in users who voted')
                ->color('secondary'),
        ];
    }
}
