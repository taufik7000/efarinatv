<?php

namespace App\Filament\Direktur\Resources\FinanceCategoryResource\Pages;

use App\Filament\Direktur\Resources\FinanceCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFinanceCategories extends ListRecords
{
    protected static string $resource = FinanceCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
