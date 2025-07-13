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

    /**
     * Meng-override metode ini untuk menambahkan logika kustom saat
     * sebuah pengajuan anggaran dibuat.
     */
    protected function handleRecordCreation(array $data): Model
    {
        try {
            // Debug log
            Log::info('Creating enhanced pengajuan anggaran', [
                'user_id' => Auth::id(),
                'project_name' => $data['project_name'] ?? 'No project name',
                'urgency_level' => $data['urgency_level'] ?? 'medium'
            ]);

            // Ambil data untuk relasi dari form
            $detailsData = $data['details'] ?? [];
            $attachmentsData = $data['attachments'] ?? [];
            
            // Hitung total dan cari ID tim "Redaksi"
            $total = 0;
            
            // Cari tim redaksi dengan error handling
            $redaksiTeam = Team::where('name', 'Redaksi')->first();
            if (!$redaksiTeam) {
                $redaksiTeam = Team::firstOrCreate(
                    ['name' => 'Redaksi'],
                    [
                        'description' => 'Tim Redaksi',
                        'department' => 'redaksi',
                        'is_active' => true
                    ]
                );
            }
            
            $redaksiTeamId = $redaksiTeam->id;

            // Loop melalui rincian untuk menghitung total dan set team_id
            foreach ($detailsData as &$detail) {
                // Hitung amount berdasarkan quantity dan unit_price jika ada
                if (isset($detail['quantity']) && isset($detail['unit_price'])) {
                    $detail['amount'] = (float)$detail['quantity'] * (float)$detail['unit_price'];
                }
                
                $total += (float) ($detail['amount'] ?? 0);
                $detail['team_id'] = $redaksiTeamId; // Otomatis set ke tim Redaksi
            }

            // Siapkan data untuk tabel transaksi utama
            $transactionData = [
                'project_name' => $data['project_name'],
                'description' => $data['description'],
                'urgency_level' => $data['urgency_level'] ?? 'medium',
                'budget_type' => $data['budget_type'] ?? null,
                'expected_completion' => $data['expected_completion'] ?? null,
                'approval_needed_by' => $data['approval_needed_by'] ?? null,
                'pic_contact' => $data['pic_contact'] ?? null,
                'additional_notes' => $data['additional_notes'] ?? null,
                'is_urgent_request' => $data['is_urgent_request'] ?? false,
                'urgent_reason' => $data['urgent_reason'] ?? null,
                'total_amount' => $total,
                'type' => 'expense',
                'status' => 'pending',
                'user_id' => Auth::id(),
                'transaction_date' => $data['transaction_date'],
            ];

            // Hapus data relasi dari array utama agar tidak error
            unset($data['details'], $data['attachments']);

            // Buat record transaksi utama
            $transaction = static::getModel()::create($transactionData);

            // Buat record untuk relasi 'details' dan 'attachments'
            if (!empty($detailsData)) {
                $transaction->details()->createMany($detailsData);
            }
            if (!empty($attachmentsData)) {
                $transaction->attachments()->createMany($attachmentsData);
            }

            Log::info('Enhanced pengajuan anggaran created successfully', [
                'transaction_id' => $transaction->id,
                'project_name' => $transaction->project_name,
                'total_amount' => $transaction->total_amount,
                'urgency_level' => $transaction->urgency_level,
                'items_count' => count($detailsData),
                'attachments_count' => count($attachmentsData)
            ]);

            // Kirim notifikasi berdasarkan tingkat urgensi
            $this->sendNotificationBasedOnUrgency($transaction);

            return $transaction;

        } catch (\Exception $e) {
            Log::error('Error creating enhanced pengajuan anggaran', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'project_name' => $data['project_name'] ?? 'Unknown'
            ]);
            
            // Tampilkan error notification ke user
            Notification::make()
                ->title('Gagal membuat pengajuan')
                ->body('Terjadi kesalahan saat membuat pengajuan anggaran. Silakan coba lagi.')
                ->danger()
                ->send();
            
            throw $e;
        }
    }

    /**
     * Kirim notifikasi berdasarkan tingkat urgensi
     */
    private function sendNotificationBasedOnUrgency($transaction)
    {
        $urgencyMessages = [
            'low' => 'Pengajuan anggaran berhasil dibuat. Proses persetujuan akan dilakukan dalam 1-2 minggu.',
            'medium' => 'Pengajuan anggaran berhasil dibuat. Proses persetujuan akan dilakukan dalam beberapa hari.',
            'high' => 'Pengajuan anggaran PENTING berhasil dibuat. Tim keuangan akan segera meninjau.',
            'urgent' => 'Pengajuan anggaran URGENT berhasil dibuat. Notifikasi segera dikirim ke manager.'
        ];

        $colors = [
            'low' => 'success',
            'medium' => 'info', 
            'high' => 'warning',
            'urgent' => 'danger'
        ];

        $message = $urgencyMessages[$transaction->urgency_level] ?? $urgencyMessages['medium'];
        $color = $colors[$transaction->urgency_level] ?? 'info';

        Notification::make()
            ->title('Pengajuan Anggaran Berhasil Dibuat')
            ->body($message)
            ->color($color)
            ->duration(8000)
            ->send();

        // Jika urgent, kirim notifikasi khusus
        if ($transaction->urgency_level === 'urgent' || $transaction->is_urgent_request) {
            Notification::make()
                ->title('⚠️ PENGAJUAN URGENT')
                ->body("Pengajuan '{$transaction->project_name}' memerlukan persetujuan segera!")
                ->danger()
                ->persistent()
                ->send();
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

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Buat Pengajuan')
                ->icon('heroicon-o-paper-airplane'),
            $this->getCancelFormAction()
                ->label('Batal')
                ->color('gray'),
        ];
    }
}