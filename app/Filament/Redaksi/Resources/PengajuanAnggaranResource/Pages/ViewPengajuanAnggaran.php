<?php

namespace App\Filament\Redaksi\Resources\PengajuanAnggaranResource\Pages;

use App\Filament\Redaksi\Resources\PengajuanAnggaranResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPengajuanAnggaran extends ViewRecord
{
    protected static string $resource = PengajuanAnggaranResource::class;

    /**
     * Menambahkan tombol aksi di header halaman view,
     * misalnya tombol Edit.
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
