<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use App\Models\Post;
use App\Models\Category;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use BackedEnum;

class CustomDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected string $view = 'filament.admin.pages.custom-dashboard';

    protected static ?string $title = 'Dashboard';

    public function getHeaderWidgets(): array
    {
        return [
             \App\Filament\Admin\Widgets\CustomStatsOverview::class,
        ];
    }
 
}
