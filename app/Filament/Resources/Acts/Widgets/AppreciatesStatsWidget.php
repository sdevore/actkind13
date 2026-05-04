<?php

namespace App\Filament\Resources\Acts\Widgets;

use App\Filament\Resources\Acts\Pages\ListActs;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AppreciatesStatsWidget extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListActs::class;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Appreciation Count', $this->getPageTableQuery()->count())
                ->color('success')
                ->chart([1, 2, 3, 4, 5, 4, 3, 2, 1]),

        ];
    }
}
