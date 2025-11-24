<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Pages\CustomDashboard;
use App\Filament\Admin\Pages\VotingAnalytics;
use App\Filament\Admin\Resources\Categories\CategoryResource;
use App\Filament\Admin\Resources\CPT\CPTResource;
use App\Filament\Admin\Resources\CustomPostTypes\CustomPostTypeResource;
use App\Filament\Admin\Resources\Pages\PageResource;
use App\Filament\Admin\Resources\Posts\PostResource;
use App\Filament\Admin\Resources\Tags\TagResource;
use App\Models\CustomPostType;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationItem;
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
            ->pages([CustomDashboard::class, VotingAnalytics::class])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\Filament\Admin\Widgets')
            ->navigationGroups([
                'Content Types',
                'Dynamic Categories',
            ])
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {

                // Safe helper to get navigation items from a class
                $getItems = fn($class) => $class::getNavigationItems() ?? [];

                // Core resources & pages
                $resourceItems = array_merge(
                    $getItems(CustomDashboard::class),
                    $getItems(VotingAnalytics::class),
                    $getItems(CategoryResource::class),
                    $getItems(PageResource::class),
                    $getItems(TagResource::class),
                    $getItems(PostResource::class),
                    $getItems(CustomPostTypeResource::class),
                );

                // Dynamic Custom Post Types - cache for 60 minutes
                $groupName = 'Content Types';

                // $cptItems = collect(cache()->remember('filament_cpt_nav_raw', 0, function () {
                //     return CustomPostType::where('enabled', true)
                //         ->orderBy('menu_order')
                //         ->get()
                //         ->map(fn($cpt) => [
                //             'label' => $cpt->singular_label,
                //             'slug' => $cpt->slug,
                //             'icon' => $cpt->icon ?? 'heroicon-o-document-text',
                //         ])
                //         ->all();
                // }))->map(
                //     fn($data) => NavigationItem::make($data['label'])
                //         ->icon($data['icon'])
                //         ->group($groupName)
                //         ->url("/admin/cpt/{$data['slug']}?post_type={$data['slug']}")

                // )->all();

                $cptItems = collect(cache()->remember('filament_cpt_nav_raw', 0, function () {
                    return CustomPostType::where('enabled', true)
                        ->orderBy('menu_order')
                        ->get()
                        ->map(fn($cpt) => [
                            'label' => $cpt->singular_label,
                            'slug' => $cpt->slug,
                            'icon' => $cpt->icon ?? 'heroicon-o-document-text',
                        ])
                        ->all();
                }))->map(
                    fn($data) => NavigationItem::make($data['label'])
                        ->icon($data['icon'])
                        ->group($groupName)
                        ->url(fn() => CPTResource::getUrl('index', [
                            'post_type' =>  $data['slug'],
                        ]))
                )->all();

                // Merge all items into one flat array
                $allItems = array_merge($resourceItems, $cptItems);

                // Assign items to the builder
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
