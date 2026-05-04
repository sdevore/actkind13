<?php

namespace App\Filament\Resources\Appreciates\Pages;

use App\Filament\Resources\Appreciates\AppreciateResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAppreciate extends EditRecord
{
    protected static string $resource = AppreciateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
