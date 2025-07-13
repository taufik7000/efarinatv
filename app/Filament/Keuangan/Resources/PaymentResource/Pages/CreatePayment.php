<?php

namespace App\Filament\Keuangan\Resources\PaymentResource\Pages;

use App\Filament\Keuangan\Resources\PaymentResource;
use App\Filament\Keuangan\Resources\FinanceTransactionResource;
use App\Models\AccountabilityReport;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getRedirectUrl(): string
    {
        return FinanceTransactionResource::getUrl('view', ['record' => $this->record->finance_transaction_id]);
    }

    protected function afterCreate(): void
    {
        $transaction = $this->record->transaction;
        if ($transaction) {
            // Update status transaksi menjadi 'paid'
            $transaction->update(['status' => 'paid']);

            // Jika ada penanggung jawab, buatkan draf laporan pertanggungjawaban
            if ($transaction->pic_user_id) {
                // 1. Buat laporan utamanya
                $report = AccountabilityReport::create([
                    'finance_transaction_id' => $transaction->id,
                    'user_id'              => $transaction->pic_user_id,
                    'report_date'          => now(),
                    'summary'              => '', // Dikosongkan untuk diisi oleh PIC
                    'status'               => 'pending_submission',
                ]);
                
                // --- AWAL PERBAIKAN ---
                // 2. Buat draf untuk setiap rinciannya
                foreach ($transaction->details as $detail) {
                    $report->details()->create([
                        'finance_transaction_detail_id' => $detail->id,
                        'actual_amount'                 => 0, // Default 0 untuk diisi nanti
                    ]);
                }
                // --- AKHIR PERBAIKAN ---
            }

            // Kirim Notifikasi
            Notification::make()
                ->title('Pembayaran Diproses')
                ->body("Pembayaran untuk '{$transaction->project_name}' telah berhasil. Diteruskan ke penanggungjawab.")
                ->success()
                ->sendToDatabase($transaction->user);
        }
    }
}