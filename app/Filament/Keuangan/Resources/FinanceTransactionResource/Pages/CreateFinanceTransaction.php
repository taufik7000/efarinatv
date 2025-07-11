<?php

namespace App\Filament\Keuangan\Resources\FinanceTransactionResource\Pages;

use App\Filament\Keuangan\Resources\FinanceTransactionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateFinanceTransaction extends CreateRecord
{
    protected static string $resource = FinanceTransactionResource::class;

    /**
     * Override metode ini untuk mengontrol proses penyimpanan secara manual.
     * Ini adalah pendekatan yang lebih andal.
     */
    protected function handleRecordCreation(array $data): Model
    {
        // 1. Pisahkan data untuk relasi (details dan attachments)
        $detailsData = $data['details'] ?? [];
        $attachmentsData = $data['attachments'] ?? [];
        
        // 2. Hitung 'total_amount' secara manual di sini untuk memastikan nilainya ada.
        if ($data['type'] === 'expense') {
            $total = 0;
            // Loop melalui rincian biaya untuk mendapatkan total
            foreach ($detailsData as $detail) {
                $total += (float) ($detail['amount'] ?? 0);
            }
            $data['total_amount'] = $total;
        }
        // Catatan: Jika tipenya 'income', 'total_amount' sudah ada di $data dari form.

        // 3. Tambahkan user_id yang membuat transaksi
        $data['user_id'] = Auth::id();

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

        return $transaction;
    }
}