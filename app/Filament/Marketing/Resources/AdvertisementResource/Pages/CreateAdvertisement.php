<?php

namespace App\Filament\Marketing\Resources\AdvertisementResource\Pages;

use App\Filament\Marketing\Resources\AdvertisementResource;
use App\Models\EditorialTask;
use App\Models\FinanceTransaction;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CreateAdvertisement extends CreateRecord
{
    protected static string $resource = AdvertisementResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Tetapkan user marketing yang membuat
        $data['marketing_user_id'] = Auth::id();
        
        // Status iklan baru akan menjadi 'pending_payment' secara default dari migrasi
        // jadi kita tidak perlu mengaturnya di sini.

        // 1. Buat record iklan utama
        $advertisement = static::getModel()::create($data);

        // 2. OTOMATIS: Buat transaksi pemasukan dengan status PENDING
        FinanceTransaction::create([
            'advertisement_id' => $advertisement->id, // Menambahkan hubungan langsung ke iklan
            'type' => 'income',
            'status' => 'pending',
            'total_amount' => $advertisement->price,
            'description' => 'Pemasukan dari iklan ' . $advertisement->title . ' (' . $advertisement->client_name . ')',
            'transaction_date' => now(),
            'user_id' => Auth::id(),
        ]);

        // 3. OTOMATIS: Buat tugas untuk tim Redaksi
        EditorialTask::create([
            'advertisement_id' => $advertisement->id,
            'description' => 'Siapkan materi untuk iklan: ' . $advertisement->title,
            'due_date' => Carbon::parse($advertisement->start_date)->subDays(3),
            'status' => 'pending',
        ]);

        return $advertisement;
    }
}