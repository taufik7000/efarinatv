<?php

namespace App\Filament\Redaksi\Resources\EditorialTaskResource\Pages;

use App\Filament\Redaksi\Resources\EditorialTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEditorialTasks extends ListRecords
{
    protected static string $resource = EditorialTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
