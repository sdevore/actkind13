<?php

namespace App\Filament\Resources\Contactuses\Pages;

use App\Filament\Resources\Contactuses\ContactUsResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListContactUs extends ListRecords
{
    protected static string $resource = ContactUsResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Contacted'),
            'un_invited' => Tab::make('Uninvited')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('invitation_id')),
            'invited' => Tab::make('Invited')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('invitation_id')),
        ];
    }
}
