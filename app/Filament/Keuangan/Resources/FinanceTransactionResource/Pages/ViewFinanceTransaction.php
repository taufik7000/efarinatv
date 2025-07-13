<?php

namespace App\Filament\Keuangan\Resources\FinanceTransactionResource\Pages;

use App\Filament\Keuangan\Resources\FinanceTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Grid;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ViewFinanceTransaction extends ViewRecord
{
    protected static string $resource = FinanceTransactionResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Transaksi')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('type')
                                ->label('Tipe Transaksi')
                                ->badge()
                                ->color(fn (string $state): string => $state === 'income' ? 'success' : 'danger')
                                ->formatStateUsing(fn (string $state): string => match ($state) {
                                    'income' => '💰 Pemasukan',
                                    'expense' => '💸 Pengeluaran',
                                    default => ucfirst($state),
                                }),

                            TextEntry::make('status')
                                ->label('Status')
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'pending' => 'warning',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'paid' => 'info',
                                    default => 'gray',
                                })
                                ->formatStateUsing(fn (string $state): string => match ($state) {
                                    'pending' => '⏳ Menunggu Persetujuan',
                                    'approved' => '✅ Disetujui',
                                    'rejected' => '❌ Ditolak',
                                    'paid' => '💰 Telah Dibayar',
                                    default => ucfirst($state),
                                }),

                            TextEntry::make('total_amount')
                                ->label('Total Amount')
                                ->money('IDR')
                                ->size('lg')
                                ->weight('bold')
                                ->color('success'),
                        ]),

                        Grid::make(2)->schema([
                            TextEntry::make('urgency_level')
                                ->label('Tingkat Urgensi')
                                ->badge()
                                ->color(fn (?string $state): string => match ($state) {
                                    'low' => 'gray',
                                    'medium' => 'info',
                                    'high' => 'warning',
                                    'urgent' => 'danger',
                                    default => 'gray',
                                })
                                ->formatStateUsing(fn (?string $state): string => match ($state) {
                                    'low' => '🔵 Rendah',
                                    'medium' => '🟡 Sedang',
                                    'high' => '🟠 Tinggi',
                                    'urgent' => '🔴 Sangat Urgent',
                                    default => '-',
                                })
                                ->default('-'),

                            IconEntry::make('is_urgent_request')
                                ->label('Permintaan Urgent')
                                ->boolean()
                                ->trueIcon('heroicon-o-exclamation-triangle')
                                ->falseIcon('heroicon-o-check-circle')
                                ->trueColor('danger')
                                ->falseColor('success'),
                        ]),

                        Grid::make(2)->schema([
                            TextEntry::make('transaction_date')
                                ->label('Tanggal Transaksi')
                                ->date('d F Y H:i')
                                ->icon('heroicon-o-calendar'),

                            TextEntry::make('budget_type')
                                ->label('Jenis Anggaran')
                                ->badge()
                                ->formatStateUsing(fn (?string $state): string => match ($state) {
                                    'operational' => 'Operasional',
                                    'project' => 'Proyek Khusus',
                                    'equipment' => 'Peralatan',
                                    'travel' => 'Perjalanan Dinas',
                                    'event' => 'Event',
                                    'emergency' => 'Darurat',
                                    'training' => 'Pelatihan',
                                    'maintenance' => 'Pemeliharaan',
                                    default => $state ?? 'Tidak Ditentukan',
                                }),
                        ]),
                    ])
                    ->columns(1),

                Section::make('Detail Proyek/Kegiatan')
                    ->schema([
                        TextEntry::make('project_name')
                            ->label('Nama Proyek/Kegiatan')
                            ->size('lg')
                            ->weight('bold')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->default('Tidak ada nama proyek'),

                        TextEntry::make('description')
                            ->label('Deskripsi')
                            ->markdown()
                            ->columnSpanFull(),

                        Grid::make(2)->schema([
                            TextEntry::make('pic_contact')
                                ->label('PIC/Contact Person')
                                ->default('Tidak ada')
                                ->icon('heroicon-o-phone'),

                            TextEntry::make('user.name')
                                ->label('Diinput Oleh')
                                ->icon('heroicon-o-user')
                                ->badge()
                                ->color('info'),
                        ]),
                    ])
                    ->visible(fn ($record) => $record->project_name || $record->pic_contact),

                Section::make('Rincian Transaksi')
                    ->schema([
                        RepeatableEntry::make('details')
                            ->label('')
                            ->schema([
                                Grid::make(4)->schema([
                                    TextEntry::make('item_description')
                                        ->label('Deskripsi Item')
                                        ->weight('bold')
                                        ->columnSpan(2),

                                    TextEntry::make('category.name')
                                        ->label('Kategori')
                                        ->badge(),

                                    TextEntry::make('team.name')
                                        ->label('Tim')
                                        ->badge()
                                        ->color('info'),
                                ]),

                                Grid::make(4)->schema([
                                    TextEntry::make('quantity')
                                        ->label('Quantity')
                                        ->default(1)
                                        ->suffix(' unit'),

                                    TextEntry::make('unit_price')
                                        ->label('Harga Satuan')
                                        ->money('IDR')
                                        ->default('0'),

                                    TextEntry::make('amount')
                                        ->label('Total Harga')
                                        ->money('IDR')
                                        ->weight('bold')
                                        ->color('success'),

                                    TextEntry::make('supplier_vendor')
                                        ->label('Supplier')
                                        ->default('-')
                                        ->badge()
                                        ->color('gray'),
                                ]),

                                TextEntry::make('justification')
                                    ->label('Justifikasi')
                                    ->default('Tidak ada justifikasi')
                                    ->columnSpanFull()
                                    ->markdown(),
                            ])
                            ->columns(1)
                            ->columnSpanFull(),

                        // Summary total
                        TextEntry::make('total_summary')
                            ->label('💰 TOTAL KESELURUHAN')
                            ->formatStateUsing(function ($record) {
                                $itemCount = $record->details->count();
                                $total = $record->total_amount;
                                return "🧾 {$itemCount} item(s) | 💵 Rp " . number_format($total);
                            })
                            ->size('lg')
                            ->weight('bold')
                            ->color('success')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record->type === 'expense' && $record->details->isNotEmpty()),

                Section::make('Informasi Persetujuan')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('created_at')
                                ->label('Tanggal Dibuat')
                                ->dateTime('d F Y, H:i')
                                ->icon('heroicon-o-calendar-days'),

                            TextEntry::make('approver.name')
                                ->label('Disetujui Oleh')
                                ->default('Belum disetujui')
                                ->icon('heroicon-o-user-circle')
                                ->badge()
                                ->color(fn ($record) => $record->approver ? 'success' : 'warning'),

                            TextEntry::make('approved_at')
                                ->label('Tanggal Disetujui')
                                ->dateTime('d F Y, H:i')
                                ->default('Belum disetujui')
                                ->icon('heroicon-o-clock'),
                        ]),
                    ]),

                Section::make('Informasi Urgent')
                    ->schema([
                        TextEntry::make('urgent_reason')
                            ->label('Alasan Urgent')
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record->is_urgent_request && $record->urgent_reason),

                Section::make('Catatan Tambahan')
                    ->schema([
                        TextEntry::make('additional_notes')
                            ->label('Catatan')
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record->additional_notes),

                Section::make('Dokumen Pendukung')
                    ->schema([
                        RepeatableEntry::make('attachments')
                            ->label('')
                            ->schema([
                                Grid::make(4)->schema([
                                    TextEntry::make('file_name')
                                        ->label('Nama File')
                                        ->url(fn ($record) => $record->file_path ? Storage::url($record->file_path) : null, true)
                                        ->icon('heroicon-o-document')
                                        ->weight('bold'),

                                    TextEntry::make('document_type')
                                        ->label('Jenis Dokumen')
                                        ->formatStateUsing(fn (?string $state): string => match ($state) {
                                            'invoice' => 'Invoice/Tagihan',
                                            'receipt' => 'Kwitansi/Bukti Bayar',
                                            'quotation' => 'Quotation/Penawaran',
                                            'contract' => 'Kontrak/Perjanjian',
                                            'specification' => 'Spesifikasi',
                                            'other' => 'Lainnya',
                                            default => $state ?? 'Tidak ditentukan',
                                        })
                                        ->badge(),

                                    TextEntry::make('formatted_file_size')
                                        ->label('Ukuran File')
                                        ->icon('heroicon-o-archive-box'),

                                    TextEntry::make('uploadedBy.name')
                                        ->label('Diupload Oleh')
                                        ->default('Unknown')
                                        ->icon('heroicon-o-user'),
                                ]),

                                TextEntry::make('document_description')
                                    ->label('Deskripsi Dokumen')
                                    ->default('Tidak ada deskripsi')
                                    ->columnSpanFull(),
                            ])
                            ->columns(1)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record->attachments && $record->attachments->isNotEmpty()),

                Section::make('Informasi Iklan Terkait')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('advertisement.title')
                                ->label('Judul Kampanye')
                                ->weight('bold')
                                ->icon('heroicon-o-megaphone'),

                            TextEntry::make('advertisement.client_name')
                                ->label('Nama Klien')
                                ->badge()
                                ->color('info'),
                        ]),

                        Grid::make(3)->schema([
                            TextEntry::make('advertisement.adType.name')
                                ->label('Jenis Iklan')
                                ->badge(),

                            TextEntry::make('advertisement.start_date')
                                ->label('Mulai Tayang')
                                ->date('d M Y'),

                            TextEntry::make('advertisement.end_date')
                                ->label('Selesai Tayang')
                                ->date('d M Y'),
                        ]),
                    ])
                    ->visible(fn ($record) => $record->advertisement_id && $record->advertisement),

                Section::make('Timeline & Tracking')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('created_at')
                                ->label('📝 Transaksi Dibuat')
                                ->dateTime('d F Y, H:i'),

                            TextEntry::make('updated_at')
                                ->label('📅 Terakhir Diupdate')
                                ->dateTime('d F Y, H:i'),
                        ]),

                        TextEntry::make('processing_info')
                            ->label('ℹ️ Informasi Proses')
                            ->formatStateUsing(function ($record) {
                                $daysSinceCreated = $record->created_at->diffInDays(now());
                                $daysUntilNeeded = $record->transaction_date->diffInDays(now(), false);
                                
                                $info = "Transaksi dibuat {$daysSinceCreated} hari yang lalu. ";
                                
                                if ($record->status === 'pending') {
                                    $info .= "⏳ Menunggu persetujuan.";
                                } elseif ($record->status === 'approved') {
                                    $approvedDays = $record->approved_at ? $record->approved_at->diffInDays(now()) : 0;
                                    $info .= "✅ Disetujui {$approvedDays} hari yang lalu.";
                                } elseif ($record->status === 'rejected') {
                                    $info .= "❌ Transaksi ditolak.";
                                } elseif ($record->status === 'paid') {
                                    $info .= "💰 Transaksi sudah lunas.";
                                }
                                
                                return $info;
                            })
                            ->columnSpanFull()
                            ->color(fn ($record) => match($record->status) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'paid' => 'info',
                                default => 'gray'
                            }),
                    ])
                    ->collapsible(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => !in_array($this->record->status, ['approved', 'paid']))
                ->icon('heroicon-o-pencil-square')
                ->color('warning'),

            Actions\ActionGroup::make([
                Actions\Action::make('approve')
                    ->label('💚 Setujui Transaksi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function () {
                        $this->record->update([
                            'status' => 'approved',
                            'approved_by' => Auth::id(),
                            'approved_at' => now(),
                        ]);

                        // Handle advertisement if exists
                        if ($this->record->advertisement_id) {
                            $advertisement = \App\Models\Advertisement::find($this->record->advertisement_id);
                            if ($advertisement) {
                                $advertisement->update(['status' => 'active']);
                                \App\Services\TaskHelper::createTaskForRedaksi($advertisement);
                            }
                        }
                    })
                    ->visible(fn () => $this->record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Transaksi')
                    ->modalDescription('Transaksi akan disetujui dan dapat dilanjutkan ke proses berikutnya.')
                    ->modalSubmitActionLabel('✅ Ya, Setujui'),

                Actions\Action::make('reject')
                    ->label('❌ Tolak Transaksi')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3)
                            ->placeholder('Jelaskan alasan mengapa transaksi ini ditolak...')
                    ])
                    ->action(function (array $data) {
                        $this->record->update([
                            'status' => 'rejected',
                            'additional_notes' => ($this->record->additional_notes ? $this->record->additional_notes . "\n\n" : '') . 
                                                "DITOLAK PADA " . now()->format('d M Y H:i') . ": " . $data['rejection_reason']
                        ]);
                    })
                    ->visible(fn () => $this->record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalSubmitActionLabel('❌ Ya, Tolak'),

                Actions\Action::make('mark_paid')
                    ->label('💰 Tandai Lunas')
                    ->icon('heroicon-o-banknotes')
                    ->color('info')
                    ->action(fn () => $this->record->update(['status' => 'paid']))
                    ->visible(fn () => $this->record->status === 'approved')
                    ->requiresConfirmation()
                    ->modalHeading('Tandai Sebagai Lunas')
                    ->modalDescription('Transaksi akan ditandai sebagai sudah dibayar/lunas.')
                    ->modalSubmitActionLabel('💰 Ya, Sudah Lunas'),
            ])
            ->label('Aksi Persetujuan')
            ->icon('heroicon-m-ellipsis-vertical')
            ->visible(fn () => in_array($this->record->status, ['pending', 'approved'])),

            Actions\Action::make('print_export')
                ->label('Print/Export')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn () => '#')
                ->openUrlInNewTab()
                ->visible(fn () => in_array($this->record->status, ['approved', 'paid'])),

            Actions\DeleteAction::make()
                ->visible(fn () => in_array($this->record->status, ['pending', 'rejected']))
                ->requiresConfirmation(),
        ];
    }
}