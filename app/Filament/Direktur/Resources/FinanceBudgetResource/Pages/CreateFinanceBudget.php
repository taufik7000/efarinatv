<?php

namespace App\Filament\Direktur\Resources\FinanceBudgetResource\Pages;

use App\Filament\Direktur\Resources\FinanceBudgetResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;



class CreateFinanceBudget extends CreateRecord
{
    protected static string $resource = FinanceBudgetResource::class;
    protected static ?string $title = 'Buat Anggaran';

    // Anda bisa menambahkan logika khusus di sini di kemudian hari jika diperlukan.
    // Contoh: Redirect ke halaman lain setelah membuat data.
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}