<?php

namespace App\Filament\Direktur\Resources\AccountabilityReportReviewResource\Pages;

use App\Filament\Direktur\Resources\AccountabilityReportReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccountabilityReportReview extends EditRecord
{
    protected static string $resource = AccountabilityReportReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
