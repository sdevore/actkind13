<?php

namespace App\Filament\Resources\Acts\Pages;

use App\Filament\Resources\Acts\ActResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAct extends ViewRecord
{
    protected static string $resource = ActResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
