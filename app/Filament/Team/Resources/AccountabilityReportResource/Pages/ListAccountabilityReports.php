<?php

namespace App\Filament\Team\Resources\AccountabilityReportResource\Pages;

use App\Filament\Team\Resources\AccountabilityReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccountabilityReports extends ListRecords
{
    protected static string $resource = AccountabilityReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
