<?php

namespace App\Filament\Direktur\Resources\AllTransactionsResource\Pages;

use App\Filament\Direktur\Resources\AllTransactionsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAllTransactions extends ListRecords
{
    protected static string $resource = AllTransactionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
