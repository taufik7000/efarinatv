<?php

namespace App\Filament\Redaksi\Widgets;

use App\Models\FinanceTransaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class PengajuanAnggaranOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';
    protected static bool $isLazy = false;
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $userId = Auth::id();
        
        // Query untuk pengajuan user yang sedang login
        $userTransactions = FinanceTransaction::where('user_id', $userId)
            ->where('type', 'expense');

        // Statistik dasar
        $totalPengajuan = $userTransactions->count();
        $totalNilai = $userTransactions->sum('total_amount');
        $pending = $userTransactions->where('status', 'pending')->count();
        $approved = $userTransactions->where('status', 'approved')->count();
        $urgent = $userTransactions->where('urgency_level', 'urgent')
            ->orWhere('is_urgent_request', true)
            ->count();

        // Pengajuan bulan ini
        $thisMonth = $userTransactions->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Rata-rata nilai pengajuan
        $avgValue = $totalPengajuan > 0 ? $totalNilai / $totalPengajuan : 0;

        return [
            Stat::make('Total Pengajuan', $totalPengajuan)
                ->description('Semua pengajuan Anda')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('info')
                ->chart([1, 3, 5, 10, 20, 40]),

            Stat::make('Menunggu Persetujuan', $pending)
                ->description($pending > 0 ? 'Perlu ditindaklanjuti' : 'Semua telah diproses')
                ->descriptionIcon('heroicon-o-clock')
                ->color($pending > 0 ? 'warning' : 'success')
                ->extraAttributes([
                    'class' => $pending > 0 ? 'animate-pulse' : '',
                ]),

            Stat::make('Total Nilai', 'Rp ' . number_format($totalNilai))
                ->description('Semua pengajuan')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('Disetujui', $approved)
                ->description($approved > 0 ? 'Pengajuan berhasil' : 'Belum ada yang disetujui')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Pengajuan Urgent', $urgent)
                ->description($urgent > 0 ? 'Memerlukan perhatian khusus' : 'Tidak ada urgent')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($urgent > 0 ? 'danger' : 'gray'),

            Stat::make('Bulan Ini', $thisMonth)
                ->description('Pengajuan di ' . now()->format('F Y'))
                ->descriptionIcon('heroicon-o-calendar')
                ->color('info'),
        ];
    }

    protected function getColumns(): int
    {
        return 3; // 3 kolom untuk tampilan yang lebih compact
    }
}