<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\TableWidget;
use App\Models\Voting;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;

class VotesPerCategory extends TableWidget
{

    protected static ?string $heading = 'Top Nominee by Category';

    protected static ?string $pollingInterval = null; // Disable automatic polling

    protected static ?string $cacheKey = 'votes_per_category_data';

    protected static int $cacheExpiryMinutes = 10; // 10 minutes

    protected int | string | array $columnSpan = 'full';

    protected $cachedTopPosts;

    protected function getTableQueryMode(): string
    {
        return 'table';
    }

    protected function getTableQuery(): Builder
    {
        // Get selected category from session
        $selectedCategory = session('votes_category_filter');

        // Create cache key based on selected category
        $cacheKey = $selectedCategory
            ? self::$cacheKey . '_' . $selectedCategory
            : self::$cacheKey . '_all';

        $cachedData = Cache::get($cacheKey);

        if ($cachedData !== null) {
            // Store the cached data in the instance so we can use it to maintain correct order
            $this->cachedTopPosts = $cachedData;

            $postIds = $cachedData->pluck('post_id')->toArray();
            return Post::whereIn('id', $postIds);
        }

        // Get posts for selected category or all categories if none selected
        if ($selectedCategory) {
            // Get all votes for the selected category
            $categoryVotes = Voting::where('category_id', $selectedCategory)
                ->select('post_id', 'category_id')
                ->get();

            // Group votes by post and count them
            $postVoteCounts = $categoryVotes
                ->groupBy('post_id')
                ->map(function ($votes) use ($selectedCategory) {
                    return [
                        'post_id' => $votes->first()->post_id,
                        'category_id' => $selectedCategory,
                        'vote_count' => $votes->count()
                    ];
                })
                ->sortByDesc('vote_count')
                ->values();
        } else {
            // Get top 3 posts per category by vote count (original behavior when no filter)
            $allVotes = Voting::select('post_id', 'category_id')
                ->get();

            $topPostsByCategory = collect();
            $categories = $allVotes->pluck('category_id')->unique();

            foreach ($categories as $categoryId) {
                $categoryVotes = $allVotes->where('category_id', $categoryId);
                $categoryPostVoteCounts = $categoryVotes
                    ->groupBy('post_id')
                    ->map(function ($votes) {
                        return [
                            'post_id' => $votes->first()->post_id,
                            'category_id' => $votes->first()->category_id,
                            'vote_count' => $votes->count()
                        ];
                    })
                    ->sortByDesc('vote_count')
                    ->take(10) // Top 3 posts per category
                    ->values();

                $topPostsByCategory = $topPostsByCategory->concat($categoryPostVoteCounts);
            }

            $postVoteCounts = $topPostsByCategory
                ->sortByDesc('vote_count')
                ->take(20) // Limit to top 15 posts overall to keep the table manageable
                ->values();
        }

        // Cache the results with the specific key
        Cache::put($cacheKey, $postVoteCounts, now()->addMinutes(self::$cacheExpiryMinutes));

        // Store the cached data in the instance so we can use it to maintain correct order
        $this->cachedTopPosts = $postVoteCounts;

        $postIds = $postVoteCounts->pluck('post_id')->toArray();

        return Post::whereIn('id', $postIds);
    }

    protected function getTableColumns(): array
    {
        return [
            \Filament\Tables\Columns\TextColumn::make('title')
                ->label('Name')
                ->searchable()
                ->sortable(),

            \Filament\Tables\Columns\TextColumn::make('category.name')
                ->label('Category')
                ->sortable(),

            \Filament\Tables\Columns\TextColumn::make('vote_count')
                ->label('Votes')
                ->getStateUsing(function ($record) {
                    // Retrieve vote count from cached data
                    $cachedData = Cache::get(self::$cacheKey);
                    if ($cachedData) {
                        $votingRecord = $cachedData->firstWhere('post_id', $record->id);
                        // Handle both object and array formats
                        if ($votingRecord) {
                            return is_object($votingRecord) ? $votingRecord->vote_count : ($votingRecord['vote_count'] ?? 0);
                        }
                    }

                    // Fallback: query the database if cache not available
                    return $record->votes()->count();
                })
                ->numeric()
                ->sortable()
                ->default(0),

        ];
    }

    public function refreshData(): void
    {
        // Get selected category from session to determine which cache to clear
        $selectedCategory = session('votes_category_filter');

        // Create cache key based on selected category
        $cacheKey = $selectedCategory
            ? self::$cacheKey . '_' . $selectedCategory
            : self::$cacheKey . '_all';

        Cache::forget($cacheKey);
        // For TableWidget, we'll just refresh by re-running the query
        // The page should handle the refresh naturally
    }

    protected function getTableHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('category_filter')
                ->label('Filter Category')
                ->icon('heroicon-o-funnel')
                ->form([
                    \Filament\Forms\Components\Select::make('selected_category')
                        ->label('Category')
                        ->options(Category::pluck('name', 'id'))
                        ->placeholder('All Categories'),
                ])
                ->action(function (array $data) {
                    // This will require page-level implementation to work properly
                    // For now, we'll just store in session
                    session(['votes_category_filter' => $data['selected_category'] ?? null]);
                })
                ->requiresConfirmation(),

            \Filament\Actions\Action::make('refresh')
                ->label('Refresh Data')
                ->icon('heroicon-o-arrow-path')
                ->action(fn() => $this->refreshData()),
        ];
    }

    protected function getTableActions(): array
    {
        return [];
    }

    public function getTableRecords(): \Illuminate\Database\Eloquent\Collection
    {
        $records = parent::getTableRecords();

        // If we have cached data, reorder the records according to the cached ranking
        if (isset($this->cachedTopPosts) && $this->cachedTopPosts) {
            $orderedRecords = collect();

            // Create a lookup for the rank of each post
            $postRanks = [];
            foreach ($this->cachedTopPosts as $index => $post) {
                $postRanks[$post['post_id']] = $index;
            }

            // Sort records based on their rank in cached data
            $records = $records->sortBy(function ($record) use ($postRanks) {
                return $postRanks[$record->id] ?? PHP_INT_MAX;
            })->values();
        }

        return $records;
    }
}
