<?php

namespace App\Filament\Direktur\Resources\FinanceBudgetResource\Pages;

use App\Filament\Direktur\Resources\FinanceBudgetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFinanceBudgets extends ListRecords
{
    protected static string $resource = FinanceBudgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
