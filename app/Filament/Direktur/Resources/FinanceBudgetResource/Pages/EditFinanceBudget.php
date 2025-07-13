<?php

namespace App\Filament\Direktur\Resources\FinanceBudgetResource\Pages;

use App\Filament\Direktur\Resources\FinanceBudgetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFinanceBudget extends EditRecord
{
    protected static string $resource = FinanceBudgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}