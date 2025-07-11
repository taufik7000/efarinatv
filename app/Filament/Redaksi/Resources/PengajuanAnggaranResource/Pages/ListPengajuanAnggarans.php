<?php

namespace App\Filament\Redaksi\Resources\PengajuanAnggaranResource\Pages;

use App\Filament\Redaksi\Resources\PengajuanAnggaranResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengajuanAnggarans extends ListRecords
{
    protected static string $resource = PengajuanAnggaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
