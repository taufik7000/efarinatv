<?php

namespace App\Filament\Marketing\Resources\PengajuanAnggaranMarketingResource\Pages;

use App\Filament\Marketing\Resources\PengajuanAnggaranMarketingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengajuanAnggaranMarketing extends EditRecord
{
    protected static string $resource = PengajuanAnggaranMarketingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}