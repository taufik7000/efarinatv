<?php

namespace App\Filament\Direktur\Resources\AllTransactionsResource\Pages;

use App\Filament\Direktur\Resources\AllTransactionsResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Grid as InfolistGrid;
use Filament\Infolists\Components\ImageEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class ViewAllTransactions extends ViewRecord
{
    protected static string $resource = AllTransactionsResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistGrid::make(3)->schema([
                    // --- KOLOM KIRI (LEBAR) ---
                    InfolistGrid::make(1)->schema([
                        Section::make('Detail Transaksi')
                            ->schema([
                                TextEntry::make('project_name')->label('Nama Proyek/Kegiatan')->size('lg')->weight('bold')->icon('heroicon-o-clipboard-document-list'),
                                TextEntry::make('description')->label('Deskripsi')->markdown()->columnSpanFull(),
                                InfolistGrid::make(2)->schema([
                                    TextEntry::make('user.name')->label('Diajukan Oleh')->icon('heroicon-o-user')->badge()->color('info'),
                                    TextEntry::make('approver.name')->label('Disetujui Oleh')->icon('heroicon-o-check-badge')->badge()->color('success')->visible(fn ($record) => $record->approver),
                                ]),
                                InfolistGrid::make(2)->schema([
                                    TextEntry::make('created_at')->label('Tanggal Pengajuan')->dateTime('d M Y, H:i')->icon('heroicon-o-calendar-days'),
                                    TextEntry::make('approved_at')->label('Tanggal Persetujuan')->dateTime('d M Y, H:i')->icon('heroicon-o-calendar')->visible(fn ($record) => $record->approved_at),
                                ]),
                            ]),
                        
                        // --- AWAL PENAMBAHAN: Rincian Anggaran ---
                        Section::make('Rincian Anggaran')
                            ->schema([
                                RepeatableEntry::make('details')
                                    ->label('')
                                    ->schema([
                                        InfolistGrid::make(4)->schema([
                                            TextEntry::make('item_description')->label('Deskripsi Item')->weight('bold')->columnSpan(2),
                                            TextEntry::make('category.name')->label('Kategori')->badge(),
                                            TextEntry::make('amount')->label('Total Harga')->money('IDR')->weight('bold')->color('success'),
                                        ]),
                                        InfolistGrid::make(4)->schema([
                                            TextEntry::make('quantity')->label('Qty')->default(1)->suffix(' unit'),
                                            TextEntry::make('unit_price')->label('Harga Satuan')->money('IDR')->default('0'),
                                        ]),
                                    ])->columns(1)->columnSpanFull(),
                            ])->visible(fn ($record) => $record->type === 'expense' && $record->details->isNotEmpty()),
                        // --- AKHIR PENAMBAHAN ---
                    ])->columnSpan(2),

                    // --- KOLOM KANAN ---
                    InfolistGrid::make(1)->schema([
                        Section::make('Status & Informasi')
                            ->schema([
                                TextEntry::make('status')->label('Status')->badge()->color(fn(string $state): string => match ($state) { 'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', 'paid' => 'info', default => 'gray', })->formatStateUsing(fn(string $state): string => match ($state) { 'pending' => 'Menunggu Persetujuan', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'paid' => 'Telah Dibayar', default => ucfirst($state), })->icon('heroicon-o-tag'),
                                TextEntry::make('budgetType.name')->label('Jenis Anggaran')->badge()->icon('heroicon-o-briefcase')->visible(fn ($record) => $record->budgetType),
                                TextEntry::make('total_amount')->label('Total Amount')->money('IDR')->size('lg')->weight('bold')->color('success')->icon('heroicon-o-banknotes'),
                            ]),
                        
                        // --- AWAL PENAMBAHAN: Informasi Pembayaran ---
                        Section::make('Informasi Pembayaran')
                            ->schema([
                                TextEntry::make('payment.payment_date')->label('Tanggal Dibayar')->date('d F Y')->icon('heroicon-o-calendar-days'),
                                TextEntry::make('payment.payment_method')->label('Metode Bayar')->badge()->formatStateUsing(fn ($state) => ucfirst(str_replace('-', ' ', $state ?? ''))),
                                TextEntry::make('payment.reference_number')->label('No. Referensi')->copyable()->icon('heroicon-o-hashtag'),
                                TextEntry::make('payment.processor.name')->label('Diproses Oleh')->icon('heroicon-o-user'),
                                ImageEntry::make('payment.proof_path')->label('Bukti Pembayaran')->height(200)->columnSpanFull(),
                                TextEntry::make('payment.proof_path')->label('')->formatStateUsing(fn() => 'Unduh Bukti Pembayaran')->url(fn($record) => $record->payment ? Storage::url($record->payment->proof_path) : null, true)->icon('heroicon-o-arrow-down-tray')->color('primary'),
                            ])->visible(fn($record) => $record->status === 'paid' && $record->payment),
                        // --- AKHIR PENAMBAHAN ---
                    ])->columnSpan(1),
                ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Setujui')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'status' => 'approved',
                        'approved_by' => Auth::id(),
                        'approved_at' => now()
                    ]);
                    Notification::make()->title('Transaksi berhasil disetujui')->success()->send();
                })
                ->visible(fn (): bool => $this->record->status === 'pending'),

            Actions\Action::make('reject')
                ->label('Tolak')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->form([Forms\Components\Textarea::make('rejection_reason')->label('Alasan Penolakan')->required(),])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => 'rejected',
                        'additional_notes' => ($this->record->additional_notes ? $this->record->additional_notes . "\n\n" : '') . "DITOLAK: " . $data['rejection_reason'],
                    ]);
                    Notification::make()->title('Transaksi telah ditolak')->danger()->send();
                })
                ->visible(fn (): bool => $this->record->status === 'pending'),
        ];
    }
}