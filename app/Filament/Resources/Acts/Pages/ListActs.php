<?php

namespace App\Filament\Resources\Acts\Pages;

use App\Enums\ActType;
use App\Filament\Resources\Acts\ActResource;
use App\Filament\Resources\Acts\Widgets\AppreciatesStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListActs extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = ActResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            AppreciatesStatsWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Acts of Kindness'),
            'submitted' => Tab::make('Did')
                ->icon(ActType::DID->getIcon())
                ->modifyQueryUsing(function ($query) {
                    return $query->where('type', ActType::DID);
                }),
            'approved' => Tab::make('Saw')
                ->icon(ActType::SAW->getIcon())
                ->modifyQueryUsing(function ($query) {
                    return $query->where('type', ActType::SAW);
                }),
            'rejected' => Tab::make('Received')
                ->icon(ActType::RECEIVED->getIcon())
                ->modifyQueryUsing(function ($query) {
                    return $query->where('type', ActType::RECEIVED);
                }),
        ];
    }
}
