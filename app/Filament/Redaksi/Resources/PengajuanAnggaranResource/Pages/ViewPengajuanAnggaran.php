<?php

namespace App\Filament\Redaksi\Resources\PengajuanAnggaranResource\Pages;

use App\Filament\Redaksi\Resources\PengajuanAnggaranResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Grid;
use Illuminate\Support\Facades\Storage;

class ViewPengajuanAnggaran extends ViewRecord
{
    protected static string $resource = PengajuanAnggaranResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Status & Informasi Utama')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('status')
                                ->label('Status Pengajuan')
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

                            TextEntry::make('urgency_level')
                                ->label('Tingkat Urgensi')
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'low' => 'gray',
                                    'medium' => 'info',
                                    'high' => 'warning',
                                    'urgent' => 'danger',
                                    default => 'gray',
                                })
                                ->formatStateUsing(fn (string $state): string => match ($state) {
                                    'low' => '🔵 Rendah',
                                    'medium' => '🟡 Sedang',
                                    'high' => '🟠 Tinggi',
                                    'urgent' => '🔴 Sangat Urgent',
                                    default => ucfirst($state),
                                }),

                            TextEntry::make('total_amount')
                                ->label('Total Anggaran')
                                ->money('IDR')
                                ->size('lg')
                                ->weight('bold')
                                ->color('success'),
                        ]),

                        Grid::make(2)->schema([
                            IconEntry::make('is_urgent_request')
                                ->label('Permintaan Urgent')
                                ->boolean()
                                ->trueIcon('heroicon-o-exclamation-triangle')
                                ->falseIcon('heroicon-o-check-circle')
                                ->trueColor('danger')
                                ->falseColor('success'),

                            TextEntry::make('budget_type')
                                ->label('Jenis Anggaran')
                                ->badge()
                                ->formatStateUsing(fn (?string $state): string => match ($state) {
                                    'operational' => 'Operasional Rutin',
                                    'project' => 'Proyek Khusus',
                                    'equipment' => 'Peralatan & Teknologi',
                                    'travel' => 'Perjalanan Dinas',
                                    'event' => 'Event & Kegiatan',
                                    'emergency' => 'Darurat',
                                    'training' => 'Pelatihan',
                                    'maintenance' => 'Pemeliharaan',
                                    default => $state ?? 'Tidak Ditentukan',
                                }),
                        ]),
                    ])
                    ->columns(1),

                Section::make('Detail Proyek')
                    ->schema([
                        TextEntry::make('project_name')
                            ->label('Nama Proyek/Kegiatan')
                            ->size('lg')
                            ->weight('bold')
                            ->icon('heroicon-o-clipboard-document-list'),

                        TextEntry::make('description')
                            ->label('Deskripsi & Tujuan')
                            ->markdown()
                            ->columnSpanFull(),

                        Grid::make(3)->schema([
                            TextEntry::make('transaction_date')
                                ->label('Tanggal Dibutuhkan')
                                ->date('d F Y')
                                ->icon('heroicon-o-calendar')
                                ->color(fn ($record) => $record->transaction_date < now() ? 'danger' : 'success'),

                            TextEntry::make('expected_completion')
                                ->label('Estimasi Penyelesaian')
                                ->default('Tidak ditentukan')
                                ->icon('heroicon-o-clock'),

                            TextEntry::make('pic_contact')
                                ->label('PIC/Contact Person')
                                ->default('Tidak ada')
                                ->icon('heroicon-o-phone'),
                        ]),
                    ]),

                Section::make('Rincian Anggaran')
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

                                    TextEntry::make('amount')
                                        ->label('Total Harga')
                                        ->money('IDR')
                                        ->weight('bold')
                                        ->color('success'),
                                ]),

                                Grid::make(4)->schema([
                                    TextEntry::make('quantity')
                                        ->label('Qty')
                                        ->default(1)
                                        ->suffix(' unit'),

                                    TextEntry::make('unit_price')
                                        ->label('Harga Satuan')
                                        ->money('IDR')
                                        ->default('0'),

                                    TextEntry::make('supplier_vendor')
                                        ->label('Supplier/Vendor')
                                        ->default('Tidak ditentukan')
                                        ->badge()
                                        ->color('gray'),

                                    TextEntry::make('justification')
                                        ->label('Justifikasi')
                                        ->default('Tidak ada')
                                        ->limit(50),
                                ]),
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
                    ]),

                Section::make('Informasi Persetujuan')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('approval_needed_by')
                                ->label('Persetujuan Diperlukan Oleh')
                                ->formatStateUsing(fn (?string $state): string => match ($state) {
                                    'manager' => 'Manager Redaksi',
                                    'finance_manager' => 'Manager Keuangan',
                                    'director' => 'Direktur',
                                    'board' => 'Dewan Direksi',
                                    default => $state ?? 'Tidak ditentukan',
                                })
                                ->badge()
                                ->icon('heroicon-o-user-circle'),

                            TextEntry::make('user.name')
                                ->label('Diajukan Oleh')
                                ->icon('heroicon-o-user')
                                ->badge()
                                ->color('info'),
                        ]),

                        TextEntry::make('created_at')
                            ->label('Tanggal Pengajuan')
                            ->dateTime('d F Y, H:i')
                            ->icon('heroicon-o-calendar-days'),

                        TextEntry::make('additional_notes')
                            ->label('Catatan Tambahan')
                            ->default('Tidak ada catatan tambahan')
                            ->markdown()
                            ->columnSpanFull(),
                    ]),

                Section::make('Informasi Urgent')
                    ->schema([
                        TextEntry::make('urgent_reason')
                            ->label('Alasan Urgent')
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record->is_urgent_request),

                Section::make('Dokumen Pendukung')
                    ->schema([
                        RepeatableEntry::make('attachments')
                            ->label('')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextEntry::make('file_name')
                                        ->label('Nama File')
                                        ->url(fn ($record) => $record->file_path ? Storage::url($record->file_path) : null, true)
                                        ->icon('heroicon-o-document')
                                        ->weight('bold'),

                                    TextEntry::make('document_type')
                                        ->label('Jenis Dokumen')
                                        ->formatStateUsing(fn (?string $state): string => match ($state) {
                                            'quotation' => 'Quotation/Penawaran',
                                            'specification' => 'Spesifikasi Teknis',
                                            'comparison' => 'Perbandingan Harga',
                                            'proposal' => 'Proposal Kegiatan',
                                            'reference' => 'Referensi/Contoh',
                                            'other' => 'Lainnya',
                                            default => $state ?? 'Tidak ditentukan',
                                        })
                                        ->badge(),

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

                Section::make('Timeline & Tracking')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('created_at')
                                ->label('📝 Pengajuan Dibuat')
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
                                
                                $info = "Pengajuan dibuat {$daysSinceCreated} hari yang lalu. ";
                                
                                if ($daysUntilNeeded > 0) {
                                    $info .= "Anggaran dibutuhkan dalam {$daysUntilNeeded} hari.";
                                } elseif ($daysUntilNeeded < 0) {
                                    $info .= "⚠️ Anggaran sudah terlambat " . abs($daysUntilNeeded) . " hari!";
                                } else {
                                    $info .= "⚠️ Anggaran dibutuhkan HARI INI!";
                                }
                                
                                return $info;
                            })
                            ->columnSpanFull()
                            ->color(fn ($record) => $record->transaction_date < now() ? 'danger' : 'info'),
                    ])
                    ->collapsible(),
            ]);
    }

    /**
     * Menambahkan tombol aksi di header halaman view.
     */
   protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => in_array($this->record->status, ['pending', 'rejected']))
                ->icon('heroicon-o-pencil-square')
                ->color('warning'),

            Actions\Action::make('print')
                ->label('Print/Export')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(fn () => route('pengajuan.print', ['pengajuan' => $this->record->id]))
                ->openUrlInNewTab(),

            Actions\Action::make('duplicate')
                ->label('Duplikasi')
                ->icon('heroicon-o-document-duplicate')
                ->color('success')
                ->action(function () {
                    $newRecord = $this->record->replicate();
                    $newRecord->status = 'pending';
                    $newRecord->project_name = $this->record->project_name . ' (Copy)';
                    $newRecord->created_at = now();
                    $newRecord->save();
                    
                    // Duplikasi details
                    foreach ($this->record->details as $detail) {
                        $newDetail = $detail->replicate();
                        $newDetail->transaction_id = $newRecord->id;
                        $newDetail->save();
                    }
                    
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $newRecord]));
                })
                ->requiresConfirmation()
                ->modalHeading('Duplikasi Pengajuan')
                ->modalDescription('Membuat salinan pengajuan ini sebagai draft baru. Lanjutkan?'),

            Actions\DeleteAction::make()
                ->visible(fn () => in_array($this->record->status, ['pending', 'rejected']))
                ->requiresConfirmation(),
        ];
    }
}