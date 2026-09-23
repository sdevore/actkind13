<?php

namespace App\Filament\Resources\Acts\Widgets;

use App\Filament\Resources\Acts\Pages\ListActs;
use App\Models\Act;
use App\Models\Appreciate;
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
            Stat::make('Appreciation Count', $this->countAppreciationsOnListedActs())
                ->color('success')
                ->chart([1, 2, 3, 4, 5, 4, 3, 2, 1]),

        ];
    }

    private function countAppreciationsOnListedActs(): int
    {
        $listedActIds = $this->getPageTableQuery()
            ->reorder()
            ->select('acts.id');

        return Appreciate::query()
            ->where('appreciable_type', (new Act)->getMorphClass())
            ->whereIn('appreciable_id', $listedActIds)
            ->count();
    }
}
