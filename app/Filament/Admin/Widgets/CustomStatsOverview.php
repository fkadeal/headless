<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Post;
use App\Models\Category;
use App\Models\User;

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
        ];
    }
}
