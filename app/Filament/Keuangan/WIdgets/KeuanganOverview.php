<?php

namespace App\Filament\Keuangan\Widgets;

use App\Models\FinanceTransaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class KeuanganOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';
    protected static bool $isLazy = false;
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Statistik dasar
        $totalTransactions = FinanceTransaction::count();
        $pendingApproval = FinanceTransaction::where('status', 'pending')->count();
        $approvedCount = FinanceTransaction::where('status', 'approved')->count();
        $urgentRequests = FinanceTransaction::where('is_urgent_request', true)
            ->orWhere('urgency_level', 'urgent')
            ->count();

        // Statistik finansial bulan ini
        $thisMonth = now()->startOfMonth();
        $monthlyIncome = FinanceTransaction::where('type', 'income')
            ->where('status', 'approved')
            ->where('created_at', '>=', $thisMonth)
            ->sum('total_amount');
            
        $monthlyExpense = FinanceTransaction::where('type', 'expense')
            ->where('status', 'approved')
            ->where('created_at', '>=', $thisMonth)
            ->sum('total_amount');

        $netCashflow = $monthlyIncome - $monthlyExpense;

        // Statistik tahun ini
        $thisYear = now()->startOfYear();
        $yearlyIncome = FinanceTransaction::where('type', 'income')
            ->where('status', 'approved')
            ->where('created_at', '>=', $thisYear)
            ->sum('total_amount');
            
        $yearlyExpense = FinanceTransaction::where('type', 'expense')
            ->where('status', 'approved')
            ->where('created_at', '>=', $thisYear)
            ->sum('total_amount');

        // Pending amount (total uang yang menunggu approval)
        $pendingAmount = FinanceTransaction::where('status', 'pending')->sum('total_amount');

        return [
            Stat::make('Menunggu Persetujuan', $pendingApproval)
                ->description($pendingApproval > 0 ? 'Transaksi perlu review' : 'Semua telah diproses')
                ->descriptionIcon('heroicon-o-clock')
                ->color($pendingApproval > 0 ? 'warning' : 'success')
                ->extraAttributes([
                    'class' => $pendingApproval > 0 ? 'animate-pulse' : '',
                ])
                ->chart([1, 3, 5, 10, 20, $pendingApproval]),

            Stat::make('Permintaan Urgent', $urgentRequests)
                ->description($urgentRequests > 0 ? 'Memerlukan perhatian segera' : 'Tidak ada urgent')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($urgentRequests > 0 ? 'danger' : 'gray')
                ->extraAttributes([
                    'class' => $urgentRequests > 0 ? 'animate-bounce' : '',
                ]),

            Stat::make('Nilai Pending', 'Rp ' . number_format($pendingAmount))
                ->description('Total nilai menunggu approval')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color($pendingAmount > 10000000 ? 'warning' : 'info'), // Warning jika > 10jt

            Stat::make('Pemasukan Bulan Ini', 'Rp ' . number_format($monthlyIncome))
                ->description('Total pemasukan ' . now()->format('F Y'))
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('success')
                ->chart([
                    FinanceTransaction::where('type', 'income')->whereMonth('created_at', now()->subMonths(5)->month)->sum('total_amount') / 1000000,
                    FinanceTransaction::where('type', 'income')->whereMonth('created_at', now()->subMonths(4)->month)->sum('total_amount') / 1000000,
                    FinanceTransaction::where('type', 'income')->whereMonth('created_at', now()->subMonths(3)->month)->sum('total_amount') / 1000000,
                    FinanceTransaction::where('type', 'income')->whereMonth('created_at', now()->subMonths(2)->month)->sum('total_amount') / 1000000,
                    FinanceTransaction::where('type', 'income')->whereMonth('created_at', now()->subMonths(1)->month)->sum('total_amount') / 1000000,
                    $monthlyIncome / 1000000,
                ]),

            Stat::make('Pengeluaran Bulan Ini', 'Rp ' . number_format($monthlyExpense))
                ->description('Total pengeluaran ' . now()->format('F Y'))
                ->descriptionIcon('heroicon-o-arrow-trending-down')
                ->color('danger')
                ->chart([
                    FinanceTransaction::where('type', 'expense')->whereMonth('created_at', now()->subMonths(5)->month)->sum('total_amount') / 1000000,
                    FinanceTransaction::where('type', 'expense')->whereMonth('created_at', now()->subMonths(4)->month)->sum('total_amount') / 1000000,
                    FinanceTransaction::where('type', 'expense')->whereMonth('created_at', now()->subMonths(3)->month)->sum('total_amount') / 1000000,
                    FinanceTransaction::where('type', 'expense')->whereMonth('created_at', now()->subMonths(2)->month)->sum('total_amount') / 1000000,
                    FinanceTransaction::where('type', 'expense')->whereMonth('created_at', now()->subMonths(1)->month)->sum('total_amount') / 1000000,
                    $monthlyExpense / 1000000,
                ]),

            Stat::make('Net Cashflow Bulan Ini', 'Rp ' . number_format($netCashflow))
                ->description($netCashflow > 0 ? 'Surplus' : ($netCashflow < 0 ? 'Defisit' : 'Break Even'))
                ->descriptionIcon($netCashflow > 0 ? 'heroicon-o-arrow-up' : ($netCashflow < 0 ? 'heroicon-o-arrow-down' : 'heroicon-o-minus'))
                ->color($netCashflow > 0 ? 'success' : ($netCashflow < 0 ? 'danger' : 'warning')),

            Stat::make('Total Transaksi', $totalTransactions)
                ->description('Semua transaksi di sistem')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('info'),

            Stat::make('Disetujui Tahun Ini', $approvedCount)
                ->description('Transaksi yang telah disetujui ' . now()->year)
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }

    protected function getColumns(): int
    {
        return 4; // 4 kolom untuk layout yang optimal
    }
}

// Widget tambahan untuk grafik detail
class KeuanganChartWidget extends \Filament\Widgets\ChartWidget
{
    protected static ?string $heading = 'Trend Keuangan 6 Bulan Terakhir';
    protected static ?int $sort = 2;
    protected static ?string $pollingInterval = '60s';

    protected function getData(): array
    {
        $months = [];
        $incomeData = [];
        $expenseData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            $income = FinanceTransaction::where('type', 'income')
                ->where('status', 'approved')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('total_amount');
                
            $expense = FinanceTransaction::where('type', 'expense')
                ->where('status', 'approved')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('total_amount');
                
            $incomeData[] = round($income / 1000000, 2); // Dalam jutaan
            $expenseData[] = round($expense / 1000000, 2); // Dalam jutaan
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan (Juta Rp)',
                    'data' => $incomeData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
                [
                    'label' => 'Pengeluaran (Juta Rp)',
                    'data' => $expenseData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                    'borderColor' => 'rgba(239, 68, 68, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Jumlah (Juta Rupiah)',
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Periode',
                    ],
                ],
            ],
            'interaction' => [
                'intersect' => false,
            ],
            'maintainAspectRatio' => false,
        ];
    }
}