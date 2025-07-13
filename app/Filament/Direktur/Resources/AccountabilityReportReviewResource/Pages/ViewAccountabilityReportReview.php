<?php

namespace App\Filament\Direktur\Resources\AccountabilityReportReviewResource\Pages;

use App\Filament\Direktur\Resources\AccountabilityReportReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;

class ViewAccountabilityReportReview extends ViewRecord
{
    protected static string $resource = AccountabilityReportReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Aksi untuk menyetujui laporan
            Actions\Action::make('approve_report')
                ->label('Setujui Laporan')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->action(fn() => $this->record->update(['status' => 'approved']))
                ->visible(fn() => $this->record->status === 'submitted'),

            // Aksi untuk meminta revisi
            Actions\Action::make('request_revision')
                ->label('Minta Revisi')
                ->color('danger')
                ->icon('heroicon-o-arrow-uturn-left')
                ->requiresConfirmation()
                ->form([
                    Components\Textarea::make('revision_notes')
                        ->label('Catatan Revisi')
                        ->required(),
                ])
                ->action(function (array $data) {
                    // Logika untuk menyimpan catatan revisi (memerlukan penambahan kolom di DB)
                    // Untuk saat ini, kita hanya ubah status
                    $this->record->update(['status' => 'revision_needed']);
                })
                ->visible(fn() => $this->record->status === 'submitted'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('Informasi Laporan')
                    ->schema([
                        Components\TextEntry::make('transaction.project_name')->label('Proyek'),
                        Components\TextEntry::make('user.name')->label('Dilaporkan oleh'),
                        Components\TextEntry::make('report_date')->date('d F Y'),
                        Components\TextEntry::make('status')->badge(),
                        TextEntry::make('actual_amount_spent')
                            ->label('Total Dana Terpakai')
                            ->money('IDR')
                            ->size('lg')
                            ->weight('bold'),
                    ])->columns(2),
                
                Components\Section::make('Ringkasan')
                    ->schema([
                        Components\TextEntry::make('summary')->markdown()->label(''),
                    ]),
                
                Components\Section::make('Rincian & Bukti Pengeluaran')
                    ->schema([
                        Components\RepeatableEntry::make('details')
                            ->schema([
                                Components\TextEntry::make('transactionDetail.item_description')->label('Item Diajukan'),
                                Components\TextEntry::make('actual_amount')->label('Biaya Aktual')->money('IDR'),
                                Components\ImageEntry::make('receipt_path')->label('Bukti'),
                            ])->columns(3),
                    ]),
            ]);
    }
}