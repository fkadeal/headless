<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Pages\CustomDashboard;
use App\Filament\Admin\Pages\VotingAnalytics;
use App\Filament\Admin\Resources\Categories\CategoryResource;
use App\Filament\Admin\Resources\CustomPostTypes\CustomPostTypeResource;
use App\Filament\Admin\Resources\Pages\PageResource;
use App\Filament\Admin\Resources\Posts\PostResource;
use App\Filament\Admin\Resources\Tags\TagResource;
use App\Models\Category;
use App\Models\Models\CustomPostType;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->sidebarFullyCollapsibleOnDesktop()
            ->path('admin')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\Filament\Admin\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\Filament\Admin\Pages')
            ->pages([ CustomDashboard::class,
                \App\Filament\Admin\Pages\VotingAnalytics::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\Filament\Admin\Widgets')
            ->navigationGroups([
                'Content Types',
                'Dynamic Categories',
            ])
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {


                $staticItems = [
        NavigationItem::make('Dashboard')
            ->icon('heroicon-o-home') // heroicon v2 name
            ->url(fn (): string => CustomDashboard::getUrl()),

        NavigationItem::make('Voting Analytics')
            ->icon('heroicon-o-chart-pie')
            ->url(fn (): string => \App\Filament\Admin\Pages\VotingAnalytics::getUrl()),
    ];

    // Resources
    $resourceItems = [
        ...CustomDashboard::getNavigationItems(),
        ...VotingAnalytics::getNavigationItems(),
        ...CategoryResource::getNavigationItems(),
        ...CustomPostTypeResource::getNavigationItems(),
        ...PageResource::getNavigationItems(),
        ...TagResource::getNavigationItems(),
        // ...PostResource::getNavigationItems(),
    ];

    // Dynamic Custom Post Types - these will redirect to the Post resource with a filter
    $cptItems = CustomPostType::where('enabled', true)
        ->orderBy('menu_order')
        ->get()
        ->map(fn($cpt) => NavigationItem::make($cpt->singular_label)
            ->icon($cpt->icon ?? 'heroicon-o-document-text')
            ->group('Content Types')
            ->url("/admin/posts?post_type={$cpt->slug}") // Filter posts by the custom post type
        )
        ->all();

    // Merge everything into one flat array
    $allItems = array_merge(
        $staticItems,
        $resourceItems,
        $cptItems
    );

    // Assign merged items to builder
    $builder->items($allItems);

    return $builder;
            })->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        // Do not add navigation in boot(); Filament does not support it here
    }
}
