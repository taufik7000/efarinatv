<?php

namespace App\Filament\Direktur\Resources\BudgetTypeResource\Pages;

use App\Filament\Direktur\Resources\BudgetTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBudgetTypes extends ListRecords
{
    protected static string $resource = BudgetTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
