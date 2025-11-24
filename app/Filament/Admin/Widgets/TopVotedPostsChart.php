<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Voting;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

class TopVotedPostsChart extends ChartWidget
{
    protected ?string $heading = 'Top 5 Voted Posts';

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $votingData = Voting::select('post_id', DB::raw('count(*) as vote_count'))
            ->groupBy('post_id')
            ->orderBy('vote_count', 'desc')
            ->limit(5)
            ->get();

        $postTitles = [];
        $voteCounts = [];

        foreach ($votingData as $voting) {
            $post = Post::find($voting->post_id);
            $postTitles[] = $post ? $post->title : 'Unknown Post';
            $voteCounts[] = $voting->vote_count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Number of Votes',
                    'data' => $voteCounts,
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 205, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                    ],
                    'borderColor' => [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $postTitles,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
