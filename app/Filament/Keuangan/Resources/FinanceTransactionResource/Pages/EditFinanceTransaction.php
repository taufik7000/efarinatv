<?php

namespace App\Filament\Keuangan\Resources\FinanceTransactionResource\Pages;

use App\Filament\Keuangan\Resources\FinanceTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFinanceTransaction extends EditRecord
{
    protected static string $resource = FinanceTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
        
        ];
    }
}
