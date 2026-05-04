<?php

namespace App\Filament\Resources\Appreciates\Pages;

use App\Filament\Resources\Appreciates\AppreciateResource;
use Filament\Resources\Pages\ListRecords;

class ListAppreciates extends ListRecords
{
    protected static string $resource = AppreciateResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
