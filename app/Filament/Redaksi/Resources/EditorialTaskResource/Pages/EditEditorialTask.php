<?php

namespace App\Filament\Redaksi\Resources\EditorialTaskResource\Pages;

use App\Filament\Redaksi\Resources\EditorialTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEditorialTask extends EditRecord
{
    protected static string $resource = EditorialTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
