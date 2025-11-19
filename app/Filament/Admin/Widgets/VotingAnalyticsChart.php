<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Models\Voting;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class VotingAnalyticsChart extends ChartWidget
{
    protected ?string $heading = 'Votes Over Time';

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $data = Trend::model(Voting::class)
            ->between(
                start: now()->subDays(30),
                end: now(),
            )
            ->perDay()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Votes',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
