<?php

namespace App\Filament\Direktur\Resources\FinanceCategoryResource\Pages;

use App\Filament\Direktur\Resources\FinanceCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFinanceCategory extends EditRecord
{
    protected static string $resource = FinanceCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
