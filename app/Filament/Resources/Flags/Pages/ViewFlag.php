<?php

namespace App\Filament\Resources\Flags\Pages;

use App\Filament\Resources\Flags\FlagResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFlag extends ViewRecord
{
    protected static string $resource = FlagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
