<?php

namespace App\Filament\Marketing\Resources\AdTypeResource\Pages;

use App\Filament\Marketing\Resources\AdTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdTypes extends ListRecords
{
    protected static string $resource = AdTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
