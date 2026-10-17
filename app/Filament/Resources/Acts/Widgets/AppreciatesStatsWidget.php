<?php

namespace App\Filament\Resources\Acts\Widgets;

use App\Filament\Resources\Acts\Pages\ListActs;
use App\Models\Act;
use App\Models\Appreciate;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Database\Eloquent\Builder;

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
            Stat::make('Appreciation Count', $this->appreciationsOnListedActs()->count())
                ->color('success')
                ->chart($this->dailyAppreciationsOnListedActs()),

        ];
    }

    /** @return array<int, int> */
    private function dailyAppreciationsOnListedActs(): array
    {
        return Trend::query($this->appreciationsOnListedActs())
            ->between(today()->subDays(13), now())
            ->perDay()
            ->count()
            ->map(fn (TrendValue $value): int => (int) $value->aggregate)
            ->all();
    }

    /** @return Builder<Appreciate> */
    private function appreciationsOnListedActs(): Builder
    {
        $listedActIds = $this->getPageTableQuery()
            ->reorder()
            ->select('acts.id');

        return Appreciate::query()
            ->where('appreciable_type', (new Act)->getMorphClass())
            ->whereIn('appreciable_id', $listedActIds);
    }
}
