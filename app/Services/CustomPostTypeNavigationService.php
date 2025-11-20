<?php

namespace App\Services;

use App\Models\Models\CustomPostType;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;

class CustomPostTypeNavigationService
{
    public static function getNavigationItems(): array
    {
        $items = [];
        
        // Get all enabled custom post types
        $customPostTypes = CustomPostType::where('enabled', true)
            ->orderBy('menu_order')
            ->get();
        
        foreach ($customPostTypes as $customPostType) {
            $items[] = NavigationItem::make($customPostType->singular_label)
                ->url(route('filament.admin.resources.custom-posts.index', [
                    'tenant' => null, // Assuming no tenant setup
                    'customPostType' => $customPostType->slug
                ]))
                ->icon('heroicon-o-document-text') // Default icon, could be configurable
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.resources.custom-posts.*'));
        }
        
        return $items;
    }
    
    public static function registerNavigationGroup(): ?NavigationGroup
    {
        $customPostTypes = CustomPostType::where('enabled', true)->get();
        
        if ($customPostTypes->count() === 0) {
            return null;
        }
        
        return NavigationGroup::make('Content Types')
            ->items(self::getNavigationItems());
    }
}