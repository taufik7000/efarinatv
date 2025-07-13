<?php

namespace App\Filament\Redaksi\Resources\PengajuanAnggaranResource\Pages;

use App\Filament\Redaksi\Resources\PengajuanAnggaranResource;
use App\Models\Team;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
            Log::info('Creating pengajuan anggaran', [
                'user_id' => Auth::id(),
                'user_roles' => Auth::user()->roles->pluck('name')->toArray(),
                'data' => $data
            ]);

            // Ambil data untuk relasi dari form
            $detailsData = $data['details'] ?? [];
            $attachmentsData = $data['attachments'] ?? [];
            
            // Hitung total dan cari ID tim "Redaksi"
            $total = 0;
            
            // Cari tim redaksi dengan error handling
            $redaksiTeam = Team::where('name', 'Redaksi')->first();
            if (!$redaksiTeam) {
                // Jika tim Redaksi tidak ada, buat atau gunakan ID default
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
                $total += (float) ($detail['amount'] ?? 0);
                $detail['team_id'] = $redaksiTeamId; // Otomatis set ke tim Redaksi
            }

            // Siapkan data untuk tabel transaksi utama
            $data['total_amount'] = $total;
            $data['type'] = 'expense';       // Semua pengajuan adalah 'expense'
            $data['status'] = 'pending';     // Status awal adalah 'pending'
            $data['user_id'] = Auth::id();   // User yang mengajukan

            // Hapus data relasi dari array utama agar tidak error
            unset($data['details'], $data['attachments']);

            // Buat record transaksi utama
            $transaction = static::getModel()::create($data);

            // Buat record untuk relasi 'details' dan 'attachments'
            if (!empty($detailsData)) {
                $transaction->details()->createMany($detailsData);
            }
            if (!empty($attachmentsData)) {
                $transaction->attachments()->createMany($attachmentsData);
            }

            Log::info('Pengajuan anggaran created successfully', [
                'transaction_id' => $transaction->id,
                'total_amount' => $transaction->total_amount
            ]);

            return $transaction;

        } catch (\Exception $e) {
            Log::error('Error creating pengajuan anggaran', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            
            throw $e;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}