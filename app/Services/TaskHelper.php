<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\User;
use App\Models\Advertisement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TaskHelper
{
    /**
     * Membuat task untuk tim Redaksi saat iklan sudah dibayar
     */
    public static function createTaskForRedaksi(Advertisement $advertisement): ?Task
    {
        $kategoriIklan = TaskCategory::where('name', 'Iklan & Promosi')->first();
        $redaksiUser = User::whereHas('roles', function ($query) {
            $query->where('name', 'redaksi');
        })->first();

        // Load relasi adType jika belum dimuat
        if (!$advertisement->relationLoaded('adType')) {
            $advertisement->load('adType');
        }

        $taskDescription = "Siapkan materi iklan untuk klien {$advertisement->client_name}.\n\n" .
                          "**Detail Iklan:**\n" .
                          "- Jenis: " . ($advertisement->adType->name ?? 'Tidak diketahui') . "\n" .
                          "- Periode Tayang: " . Carbon::parse($advertisement->start_date)->format('d M Y') . 
                          " - " . Carbon::parse($advertisement->end_date)->format('d M Y') . "\n" .
                          "- Budget: Rp " . number_format($advertisement->price) . "\n";

        // Tambahkan informasi tambahan jika tersedia
        if ($advertisement->description) {
            $taskDescription .= "- Deskripsi Kampanye: {$advertisement->description}\n";
        }
        if ($advertisement->frequency_per_day) {
            $taskDescription .= "- Frekuensi Tayang: {$advertisement->frequency_per_day}x per hari\n";
        }
        if ($advertisement->time_slots) {
            $taskDescription .= "- Slot Waktu: {$advertisement->time_slots}\n";
        }

        $taskDescription .= "\n**Brief Konten:**\n" .
                           ($advertisement->content_brief ?: "Tidak ada brief khusus. Koordinasi dengan tim marketing untuk detail lebih lanjut.") . "\n\n";

        if ($advertisement->target_audience) {
            $taskDescription .= "**Target Audience:**\n{$advertisement->target_audience}\n\n";
        }

        if ($advertisement->key_message) {
            $taskDescription .= "**Pesan Utama:**\n{$advertisement->key_message}\n\n";
        }

        if ($advertisement->special_requirements) {
            $taskDescription .= "**Persyaratan Khusus:**\n{$advertisement->special_requirements}\n\n";
        }

        $taskDescription .= "**Yang Perlu Disiapkan:**\n" .
                           "- Script/konten iklan\n" .
                           "- Desain visual (jika diperlukan)\n" .
                           "- Jadwal penayangan\n" .
                           "- Koordinasi dengan tim teknis";

        return Task::create([
            'title' => 'Siapkan Materi Iklan: ' . $advertisement->title,
            'description' => $taskDescription,
            'department' => 'redaksi',
            'category_id' => $kategoriIklan?->id,
            'priority' => 'high',
            'status' => 'todo',
            'created_by' => Auth::id(),
            'assigned_to' => $redaksiUser?->id,
            'advertisement_id' => $advertisement->id,
            'due_date' => Carbon::parse($advertisement->start_date)->subDays(3),
            'notes' => 'Tugas ini dibuat otomatis setelah pembayaran iklan dikonfirmasi. Harap koordinasi dengan tim marketing untuk detail tambahan yang diperlukan.'
        ]);
    }

    /**
     * Membuat task untuk tim Marketing (follow up pembayaran)
     */
    public static function createTaskForMarketing(Advertisement $advertisement): ?Task
    {
        $kategoriAdministrasi = TaskCategory::where('name', 'Administrasi')->first();

        return Task::create([
            'title' => 'Follow Up Pembayaran: ' . $advertisement->client_name,
            'description' => "Follow up pembayaran iklan dari klien {$advertisement->client_name}.\n\n" .
                           "**Detail:**\n" .
                           "- Iklan: {$advertisement->title}\n" .
                           "- Nilai: Rp " . number_format($advertisement->price) . "\n" .
                           "- Mulai Tayang: " . Carbon::parse($advertisement->start_date)->format('d M Y') . "\n\n" .
                           "**Action Items:**\n" .
                           "- Kirim invoice ke klien\n" .
                           "- Follow up pembayaran\n" .
                           "- Update status di sistem setelah pembayaran diterima\n" .
                           "- Koordinasi dengan tim keuangan",
            'department' => 'marketing',
            'category_id' => $kategoriAdministrasi?->id,
            'priority' => 'normal',
            'status' => 'todo',
            'created_by' => Auth::id(),
            'assigned_to' => Auth::id(), // Assign ke diri sendiri
            'advertisement_id' => $advertisement->id,
            'due_date' => now()->addDays(1), // Follow up dalam 1 hari
            'notes' => 'Pastikan pembayaran diterima sebelum tanggal mulai penayangan.'
        ]);
    }

    /**
     * Membuat task untuk keuangan saat ada transaksi pending
     */
    public static function createTaskForKeuangan(Advertisement $advertisement): ?Task
    {
        $kategoriKeuangan = TaskCategory::where('name', 'Keuangan')->first();
        $keuanganUser = User::whereHas('roles', function ($query) {
            $query->where('name', 'keuangan');
        })->first();

        return Task::create([
            'title' => 'Verifikasi Pembayaran Iklan: ' . $advertisement->client_name,
            'description' => "Verifikasi pembayaran iklan dari klien {$advertisement->client_name}.\n\n" .
                           "**Detail Iklan:**\n" .
                           "- Kampanye: {$advertisement->title}\n" .
                           "- Nilai: Rp " . number_format($advertisement->price) . "\n" .
                           "- Metode Pembayaran: " . ($advertisement->payment_method ? ucfirst($advertisement->payment_method) : 'Belum ditentukan') . "\n\n" .
                           "**Yang Perlu Dilakukan:**\n" .
                           "- Cek bukti pembayaran\n" .
                           "- Verifikasi nominal di rekening\n" .
                           "- Approve transaksi di sistem\n" .
                           "- Informasikan ke tim marketing dan redaksi",
            'department' => 'keuangan',
            'category_id' => $kategoriKeuangan?->id,
            'priority' => 'high',
            'status' => 'todo',
            'created_by' => Auth::id(),
            'assigned_to' => $keuanganUser?->id,
            'advertisement_id' => $advertisement->id,
            'due_date' => $advertisement->payment_due_date ? Carbon::parse($advertisement->payment_due_date) : now()->addDays(2),
            'notes' => 'Task ini dibuat otomatis saat ada iklan baru yang perlu verifikasi pembayaran.'
        ]);
    }

    /**
     * Update task status saat iklan selesai
     */
    public static function completeAdvertisementTasks(Advertisement $advertisement): void
    {
        Task::where('advertisement_id', $advertisement->id)
            ->whereIn('status', ['todo', 'in_progress', 'review'])
            ->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);
    }

    /**
     * Cancel task saat iklan dibatalkan
     */
    public static function cancelAdvertisementTasks(Advertisement $advertisement): void
    {
        Task::where('advertisement_id', $advertisement->id)
            ->whereIn('status', ['todo', 'in_progress', 'review'])
            ->update([
                'status' => 'cancelled'
            ]);
    }
}