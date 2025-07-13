<?php

namespace App\Filament\Keuangan\Resources\PaymentResource\Pages;

use App\Filament\Keuangan\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Detail Pembayaran')
                    ->schema([
                        TextEntry::make('transaction.project_name')
                            ->label('Untuk Transaksi Proyek'),
                        TextEntry::make('transaction.total_amount')
                            ->label('Jumlah Dibayar')
                            ->money('IDR'),
                        TextEntry::make('payment_date')
                            ->label('Tanggal Pembayaran')
                            ->date('d F Y'),
                        TextEntry::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('-', ' ', $state))),
                        TextEntry::make('reference_number')
                            ->label('Nomor Referensi'),
                        TextEntry::make('processor.name')
                            ->label('Diproses Oleh'),
                        TextEntry::make('notes')
                            ->label('Catatan')
                            ->columnSpanFull(),
                    ])->columns(2),
                
                Section::make('Bukti Pembayaran')
                    ->schema([
                        ImageEntry::make('proof_path')
                            ->label('')
                            ->height(400)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}