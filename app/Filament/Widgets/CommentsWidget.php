<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Comments\Pages\ListComments;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class CommentsWidget extends ChartWidget
{
    use InteractsWithPageTable;

    protected ?string $heading = 'Comments';

    public ?string $filter = '3months';

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '200px';

    protected ?string $pollingInterval = '60s';

    protected function getFilters(): ?array
    {
        return [
            'week' => 'Last Week',
            'month' => 'Last Month',
            '3months' => 'Last 3 Months',
        ];
    }

    protected function getTablePage(): string
    {
        return ListComments::class;
    }

    protected function getData(): array
    {
        $filter = $this->filter;
        $query = $this->getPageTableQuery();
        $query->getQuery()->orders = [];
        $data = collect();
        match ($filter) {
            'week' => $data = Trend::query($query)
                ->between(now()->subWeek(), now())
                ->perDay()
                ->count(),
            'month' => $data = Trend::query($query)
                ->between(now()->subMonth(), now())
                ->perDay()
                ->count(),
            '3months' => $data = Trend::query($query)
                ->between(now()->subMonths(3), now())
                ->perMonth()
                ->count()
        };

        return [
            'datasets' => [
                [
                    'label' => 'Comments on Acts',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
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
