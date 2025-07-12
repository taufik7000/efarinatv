<?php

namespace App\Filament\Marketing\Resources\AdvertisementResource\Pages;

use App\Filament\Marketing\Resources\AdvertisementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdvertisement extends EditRecord
{
    protected static string $resource = AdvertisementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->status === 'pending_payment'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Jika reference_materials adalah string JSON, decode menjadi array
        if (isset($data['reference_materials']) && is_string($data['reference_materials'])) {
            $data['reference_materials'] = json_decode($data['reference_materials'], true) ?? [];
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Hitung durasi hari otomatis jika start_date dan end_date ada
        if (isset($data['start_date']) && isset($data['end_date'])) {
            $startDate = \Carbon\Carbon::parse($data['start_date']);
            $endDate = \Carbon\Carbon::parse($data['end_date']);
            $data['duration_days'] = $startDate->diffInDays($endDate) + 1;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}