<?php

namespace App\Filament\Direktur\Resources\AccountabilityReportReviewResource\Pages;

use App\Filament\Direktur\Resources\AccountabilityReportReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccountabilityReportReviews extends ListRecords
{
    protected static string $resource = AccountabilityReportReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
