<?php

namespace App\Filament\Team\Resources\AccountabilityReportResource\Pages;

use App\Filament\Team\Resources\AccountabilityReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditAccountabilityReport extends EditRecord
{
    protected static string $resource = AccountabilityReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                // Laporan hanya bisa dihapus jika belum pernah disubmit
                ->visible(fn ($record) => $record->status === 'pending_submission'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        // Kembali ke halaman daftar setelah menyimpan
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        // Memberikan notifikasi kustom setelah laporan disimpan
        return Notification::make()
            ->success()
            ->title('Laporan Berhasil Dikirim')
            ->body('Laporan pertanggungjawaban Anda telah berhasil dikirim untuk ditinjau.');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Otomatis mengubah status menjadi 'submitted' saat laporan disimpan
        $data['status'] = 'submitted';
        return $data;
    }
}