<?php

namespace App\Filament\Widgets;

use App\Enums\ActType;
use App\Filament\Resources\Acts\Pages\ListActs;
use Filament\Support\Colors\Color;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ActsWidget extends ChartWidget
{
    use InteractsWithPageTable;

    protected ?string $heading = 'Acts';

    public ?string $filter = '3months';

    protected int|string|array $columnSpan = 'full';

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
        return ListActs::class;
    }

    protected function getData(): array
    {
        $filter = $this->filter;
        $query = $this->getPageTableQuery();
        $query->getQuery()->orders = [];
        $saw = collect();
        $did = collect();
        $received = collect();
        match ($filter) {
            'week' => $saw = Trend::query($query->where('type', '=', ActType::SAW))
                ->between(now()->subWeek(), now())
                ->perDay()
                ->count(),
            'month' => $saw = Trend::query($query->where('type', '=', ActType::SAW))
                ->between(now()->subMonth(), now())
                ->perDay()
                ->count(),
            '3months' => $saw = Trend::query($query->where('type', '=', ActType::SAW))
                ->between(now()->subMonths(3), now())
                ->perMonth()
                ->count()
        };
        $query = $this->getPageTableQuery();
        $query->getQuery()->orders = [];

        match ($filter) {
            'week' => $did = Trend::query($query->where('type', '=', ActType::DID))
                ->between(now()->subWeek(), now())
                ->perDay()
                ->count(),
            'month' => $did = Trend::query($query->where('type', '=', ActType::DID))
                ->between(now()->subMonth(), now())
                ->perDay()
                ->count(),
            '3months' => $did = Trend::query($query->where('type', '=', ActType::DID))
                ->between(now()->subMonths(3), now())
                ->perMonth()
                ->count()
        };
        $query = $this->getPageTableQuery();
        $query->getQuery()->orders = [];

        match ($filter) {
            'week' => $received = Trend::query($query->where('type', '=', ActType::RECEIVED))
                ->between(now()->subWeek(), now())
                ->perDay()
                ->count(),
            'month' => $received = Trend::query($query->where('type', '=', ActType::RECEIVED))
                ->between(now()->subMonth(), now())
                ->perDay()
                ->count(),
            '3months' => $received = Trend::query($query->where('type', '=', ActType::RECEIVED))
                ->between(now()->subMonths(3), now())
                ->perMonth()
                ->count()
        };
        $test = Color::convertToRgb(ActType::SAW->getColor()[600]);

        return [
            'datasets' => [
                [
                    'label' => 'Saw',
                    'backgroundColor' => Color::convertToRgb(ActType::SAW->getColor()[600]),
                    'borderColor' => Color::convertToRgb(ActType::SAW->getColor()[600]),
                    'hoverBackgroundColor' => Color::convertToRgb(ActType::SAW->getColor()[600]),
                    'color' => Color::convertToRgb(ActType::SAW->getColor()[600]),
                    'data' => $saw->map(fn (TrendValue $value) => $value->aggregate),
                ],
                [
                    'label' => 'Did',
                    'backgroundColor' => Color::convertToRgb(ActType::DID->getColor()[600]),
                    'borderColor' => Color::convertToRgb(ActType::DID->getColor()[600]),
                    'data' => $did->map(fn (TrendValue $value) => $value->aggregate),
                ],
                [
                    'label' => 'Received',
                    'backgroundColor' => Color::convertToRgb(ActType::RECEIVED->getColor()[600]),
                    'borderColor' => Color::convertToRgb(ActType::RECEIVED->getColor()[600]),
                    'data' => $received->map(fn (TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $saw->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
