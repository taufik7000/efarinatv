<?php

namespace App\Filament\Team\Resources\AccountabilityReportResource\Pages;

use App\Filament\Team\Resources\AccountabilityReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Grid;

class ViewAccountabilityReport extends ViewRecord
{
    protected static string $resource = AccountabilityReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Laporan')
                    ->schema([
                        TextEntry::make('transaction.project_name')->label('Untuk Transaksi Proyek'),
                        TextEntry::make('report_date')->label('Tanggal Laporan')->date('d F Y'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('user.name')->label('Dibuat Oleh'),
                    ])->columns(2),
                
                Section::make('Ringkasan Penggunaan Dana')
                    ->schema([
                        TextEntry::make('summary')->label('')->markdown(),
                    ]),
                
                Section::make('Rincian & Bukti Pengeluaran')
                    ->schema([
                        RepeatableEntry::make('details')
                            ->label('')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextEntry::make('transactionDetail.item_description')->label('Item'),
                                    TextEntry::make('actual_amount')->label('Biaya Aktual')->money('IDR'),
                                ]),
                                ImageEntry::make('receipt_path')->label('Bukti')->height(100),
                                TextEntry::make('notes')->label('Catatan'),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }
}