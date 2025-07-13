<?php

namespace App\Filament\Keuangan\Resources\FinanceTransactionResource\Pages;

use App\Filament\Keuangan\Resources\FinanceTransactionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class CreateFinanceTransaction extends CreateRecord
{
    protected static string $resource = FinanceTransactionResource::class;

    /**
     * Override metode ini untuk mengontrol proses penyimpanan secara manual.
     * Ini adalah pendekatan yang lebih andal.
     */
    protected function handleRecordCreation(array $data): Model
    {
        try {
            Log::info('Creating finance transaction from Keuangan panel', [
                'user_id' => Auth::id(),
                'type' => $data['type'] ?? 'unknown',
                'project_name' => $data['project_name'] ?? 'No project'
            ]);

            // 1. Pisahkan data untuk relasi (details dan attachments)
            $detailsData = $data['details'] ?? [];
            $attachmentsData = $data['attachments'] ?? [];
            
            // 2. Hitung 'total_amount' secara manual untuk expense
            if ($data['type'] === 'expense') {
                $total = 0;
                // Loop melalui rincian biaya untuk mendapatkan total
                foreach ($detailsData as $detail) {
                    $total += (float) ($detail['amount'] ?? 0);
                }
                $data['total_amount'] = $total;
            }
            // Catatan: Jika tipenya 'income', 'total_amount' sudah ada di $data dari form.

            // 3. Set default values
            $data['user_id'] = Auth::id();
            
            // Set status default berdasarkan role
            if (!isset($data['status'])) {
                $data['status'] = 'approved'; // Tim keuangan bisa langsung approve
            }
            
            // Jika langsung di-approve, set approved info
            if ($data['status'] === 'approved') {
                $data['approved_by'] = Auth::id();
                $data['approved_at'] = now();
            }

            // 4. Hapus data relasi dari array utama agar tidak error saat create
            unset($data['details'], $data['attachments']);

            // 5. Buat record transaksi utama dengan data yang sudah lengkap
            $transaction = static::getModel()::create($data);

            // 6. Jika ada, buat record untuk relasi 'details' dan 'attachments'
            if (!empty($detailsData)) {
                $transaction->details()->createMany($detailsData);
            }

            if (!empty($attachmentsData)) {
                $transaction->attachments()->createMany($attachmentsData);
            }

            Log::info('Finance transaction created successfully from Keuangan panel', [
                'transaction_id' => $transaction->id,
                'type' => $transaction->type,
                'total_amount' => $transaction->total_amount,
                'status' => $transaction->status,
                'items_count' => count($detailsData),
                'attachments_count' => count($attachmentsData)
            ]);

            // Kirim notifikasi berdasarkan jenis transaksi
            $this->sendNotificationBasedOnType($transaction);

            return $transaction;

        } catch (\Exception $e) {
            Log::error('Error creating finance transaction from Keuangan panel', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'data' => $data
            ]);
            
            // Tampilkan error notification ke user
            Notification::make()
                ->title('Gagal membuat transaksi')
                ->body('Terjadi kesalahan saat membuat transaksi keuangan. Silakan coba lagi.')
                ->danger()
                ->send();
            
            throw $e;
        }
    }

    /**
     * Kirim notifikasi berdasarkan jenis transaksi
     */
    private function sendNotificationBasedOnType($transaction)
    {
        if ($transaction->type === 'income') {
            Notification::make()
                ->title('Transaksi Pemasukan Berhasil Dibuat')
                ->body("Pemasukan sebesar Rp " . number_format($transaction->total_amount) . " telah dicatat.")
                ->success()
                ->duration(5000)
                ->send();
        } else {
            $urgencyMessages = [
                'low' => 'Transaksi pengeluaran berhasil dibuat dan telah disetujui.',
                'medium' => 'Transaksi pengeluaran berhasil dibuat dengan prioritas sedang.',
                'high' => 'Transaksi pengeluaran PENTING berhasil dibuat dan disetujui.',
                'urgent' => 'Transaksi pengeluaran URGENT berhasil dibuat dan disetujui segera.'
            ];

            $colors = [
                'low' => 'success',
                'medium' => 'info',
                'high' => 'warning', 
                'urgent' => 'danger'
            ];

            $message = $urgencyMessages[$transaction->urgency_level ?? 'medium'] ?? $urgencyMessages['medium'];
            $color = $colors[$transaction->urgency_level ?? 'medium'] ?? 'info';

            Notification::make()
                ->title('Transaksi Pengeluaran Berhasil Dibuat')
                ->body($message)
                ->color($color)
                ->duration(8000)
                ->send();

            // Jika urgent, kirim notifikasi khusus
            if ($transaction->urgency_level === 'urgent' || $transaction->is_urgent_request) {
                Notification::make()
                    ->title('⚠️ TRANSAKSI URGENT')
                    ->body("Transaksi '{$transaction->project_name}' memerlukan perhatian khusus!")
                    ->danger()
                    ->persistent()
                    ->send();
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Transaksi keuangan berhasil dibuat';
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Buat Transaksi')
                ->icon('heroicon-o-banknotes'),
            $this->getCancelFormAction()
                ->label('Batal')
                ->color('gray'),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Validasi tambahan sebelum create
        if ($data['type'] === 'expense' && empty($data['details'])) {
            throw new \Exception('Transaksi pengeluaran harus memiliki minimal 1 rincian biaya.');
        }

        if ($data['type'] === 'income' && (!isset($data['total_amount']) || $data['total_amount'] <= 0)) {
            throw new \Exception('Transaksi pemasukan harus memiliki nilai yang valid.');
        }

        return $data;
    }
}