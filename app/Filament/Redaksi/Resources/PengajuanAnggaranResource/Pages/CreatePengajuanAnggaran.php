<?php

namespace App\Filament\Redaksi\Resources\PengajuanAnggaranResource\Pages;

use App\Filament\Redaksi\Resources\PengajuanAnggaranResource;
use App\Models\Team;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class CreatePengajuanAnggaran extends CreateRecord
{
    protected static string $resource = PengajuanAnggaranResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        try {
            $detailsData = $data['details'] ?? [];
            $attachmentsData = $data['attachments'] ?? [];
            
            $total = 0;
            $redaksiTeamId = Team::where('name', 'Redaksi')->firstOrFail()->id; 

            foreach ($detailsData as &$detail) {
                $total += (float) ($detail['amount'] ?? 0);
                $detail['team_id'] = $redaksiTeamId;
            }

            // --- AWAL PERBAIKAN ---
            // Menyiapkan data untuk tabel transaksi utama dengan field yang benar
            $transactionData = [
                'project_name' => $data['project_name'],
                'description' => $data['description'],
                'urgency_level' => $data['urgency_level'] ?? 'medium',
                'budget_type_id' => $data['budget_type_id'] ?? null,
                'expected_completion' => $data['expected_completion'] ?? null,
                'approval_needed_by' => $data['approval_needed_by'] ?? null,
                'pic_user_id' => $data['pic_user_id'] ?? null, // Menggunakan pic_user_id
                'additional_notes' => $data['additional_notes'] ?? null,
                'is_urgent_request' => $data['is_urgent_request'] ?? false,
                'urgent_reason' => $data['urgent_reason'] ?? null,
                'total_amount' => $total,
                'type' => 'expense',
                'status' => 'pending',
                'user_id' => Auth::id(),
                'transaction_date' => $data['transaction_date'],
            ];
            // --- AKHIR PERBAIKAN ---

            unset($data['details'], $data['attachments']);

            $transaction = static::getModel()::create($transactionData);

            if (!empty($detailsData)) {
                $transaction->details()->createMany($detailsData);
            }
            if (!empty($attachmentsData)) {
                $transaction->attachments()->createMany($attachmentsData);
            }
            
            Log::info('Pengajuan anggaran dibuat', ['transaction_id' => $transaction->id]);

            Notification::make()
                ->title('Pengajuan Anggaran Berhasil Dibuat')
                ->body('Pengajuan Anda telah berhasil dikirim dan akan segera ditinjau.')
                ->success()
                ->send();

            return $transaction;

        } catch (\Exception $e) {
            Log::error('Error membuat pengajuan anggaran', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            Notification::make()
                ->title('Gagal membuat pengajuan')
                ->body('Terjadi kesalahan saat menyimpan data. Silakan coba lagi.')
                ->danger()
                ->send();
            
            throw $e;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Pengajuan anggaran berhasil dibuat';
    }
}