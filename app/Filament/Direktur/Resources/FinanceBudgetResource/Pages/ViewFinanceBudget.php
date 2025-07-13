<?php

namespace App\Filament\Direktur\Resources\FinanceBudgetResource\Pages;

use App\Filament\Direktur\Resources\FinanceBudgetResource;
use App\Models\FinanceCategory;
use App\Models\FinanceTransaction;
use App\Models\FinanceTransactionDetail;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;

class ViewFinanceBudget extends ViewRecord
{
    protected static string $resource = FinanceBudgetResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Anggaran')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('budgetType.name')->label('Jenis Anggaran')->badge(),
                            TextEntry::make('year')->label('Tahun Anggaran'),
                            TextEntry::make('period_type')->label('Periode')->badge()->formatStateUsing(fn(string $state) => ucfirst($state)),
                        ]),
                    ])->collapsible(),
                
                Section::make('Ringkasan Keuangan')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('amount')->label('Total Alokasi')->money('IDR')->size('lg')->color('success'),
                            TextEntry::make('used_amount')->label('Dana Terpakai')->money('IDR')->size('lg')->color('danger'),
                            TextEntry::make('remaining_budget')->label('Sisa Dana')->money('IDR')->size('lg')->color('warning')
                                ->state(fn ($record) => $record->amount - $record->used_amount),
                        ]),
                    ])->collapsible(),

                Section::make('Rincian Penggunaan Dana')
                    ->description('Daftar transaksi yang menggunakan alokasi dana dari jenis anggaran ini.')
                    ->schema([
                        RepeatableEntry::make('transactions')
                            ->label('')
                            ->schema([
                                TextEntry::make('project_name')->label('Proyek/Kegiatan')->weight('bold'),
                                TextEntry::make('transaction_date')->label('Tanggal Transaksi')->date('d M Y'),
                                TextEntry::make('total_amount')->label('Jumlah Transaksi')->money('IDR'),
                                TextEntry::make('user.name')->label('Diajukan Oleh')->badge(),
                            ])
                            ->columns(4)
                            // Logika untuk memuat transaksi yang relevan
                            ->state(function ($record) {
                                $categoryIds = FinanceCategory::where('budget_type_id', $record->budget_type_id)->pluck('id');
                                $transactionIds = FinanceTransactionDetail::whereIn('category_id', $categoryIds)->pluck('transaction_id')->unique();
                                return FinanceTransaction::whereIn('id', $transactionIds)
                                    ->whereYear('transaction_date', $record->year)
                                    ->whereIn('status', ['approved', 'paid'])
                                    ->get();
                            }),
                    ])->collapsible(),
            ]);
    }
}