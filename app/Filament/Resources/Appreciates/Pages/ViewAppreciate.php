<?php

namespace App\Filament\Resources\Appreciates\Pages;

use App\Filament\Resources\Appreciates\AppreciateResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAppreciate extends ViewRecord
{
    protected static string $resource = AppreciateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
