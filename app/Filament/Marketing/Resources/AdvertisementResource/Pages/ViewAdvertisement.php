<?php

namespace App\Filament\Marketing\Resources\AdvertisementResource\Pages;

use App\Filament\Marketing\Resources\AdvertisementResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Actions;

class ViewAdvertisement extends ViewRecord
{
    protected static string $resource = AdvertisementResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Umum')
                    ->schema([
                        TextEntry::make('ad_code')
                            ->label('Kode Iklan')
                            ->copyable()
                            ->badge(),
                        
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending_payment' => 'warning',
                                'active' => 'success',
                                'completed' => 'info',
                                'cancelled' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'pending_payment' => 'Menunggu Pembayaran',
                                'active' => 'Aktif',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                                default => $state,
                            }),
                        
                        TextEntry::make('title')
                            ->label('Judul Kampanye'),
                        
                        TextEntry::make('adType.name')
                            ->label('Jenis Iklan')
                            ->badge(),
                        
                        TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime('d M Y H:i'),
                        
                        TextEntry::make('marketingUser.name')
                            ->label('PIC Marketing'),
                    ])
                    ->columns(3),

                Section::make('Informasi Klien')
                    ->schema([
                        TextEntry::make('client_name')
                            ->label('Nama Klien/Brand'),
                        
                        TextEntry::make('client_contact')
                            ->label('Kontak Klien')
                            ->formatStateUsing(fn ($state) => $state ?: '-'),
                        
                        TextEntry::make('description')
                            ->label('Deskripsi Kampanye')
                            ->formatStateUsing(fn ($state) => $state ?: '-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Periode & Penayangan')
                    ->schema([
                        TextEntry::make('start_date')
                            ->label('Mulai Tayang')
                            ->dateTime('d M Y H:i'),
                        
                        TextEntry::make('end_date')
                            ->label('Selesai Tayang')
                            ->dateTime('d M Y H:i'),
                        
                        TextEntry::make('frequency_per_day')
                            ->label('Frekuensi per Hari')
                            ->formatStateUsing(fn ($state) => $state ? $state . 'x per hari' : '-'),
                        
                        TextEntry::make('time_slots')
                            ->label('Slot Waktu Tayang')
                            ->formatStateUsing(fn ($state) => $state ?: '-'),
                        
                        TextEntry::make('price')
                            ->label('Total Biaya')
                            ->money('IDR'),
                    ])
                    ->columns(3),

                Section::make('Brief Konten')
                    ->schema([
                        TextEntry::make('content_brief')
                            ->label('Brief untuk Tim Redaksi')
                            ->formatStateUsing(fn ($state) => $state ?: 'Tidak ada brief khusus')
                            ->columnSpanFull(),
                        
                        TextEntry::make('target_audience')
                            ->label('Target Audience')
                            ->formatStateUsing(fn ($state) => $state ?: '-'),
                        
                        TextEntry::make('key_message')
                            ->label('Pesan Utama')
                            ->formatStateUsing(fn ($state) => $state ?: '-'),
                        
                        TextEntry::make('special_requirements')
                            ->label('Persyaratan Khusus')
                            ->formatStateUsing(fn ($state) => $state ?: '-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Materi Referensi')
                    ->schema([
                        TextEntry::make('reference_materials')
                            ->label('File Referensi')
                            ->formatStateUsing(function ($state) {
                                if (!$state || !is_array($state)) return 'Tidak ada file referensi';
                                
                                $files = collect($state)->map(function ($file) {
                                    $filename = basename($file);
                                    return "• {$filename}";
                                })->join("\n");
                                
                                return $files;
                            })
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record->reference_materials && count($record->reference_materials) > 0),

                Section::make('Informasi Pembayaran')
                    ->schema([
                        TextEntry::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->formatStateUsing(fn ($state) => match($state) {
                                'cash' => 'Tunai',
                                'transfer' => 'Transfer Bank',
                                'credit' => 'Kredit/Tempo',
                                'other' => 'Lainnya',
                                default => $state ?: '-'
                            })
                            ->badge(),
                        
                        TextEntry::make('payment_terms')
                            ->label('Termin Pembayaran')
                            ->formatStateUsing(fn ($state) => $state ?: 'Pembayaran Lunas'),
                        
                        TextEntry::make('payment_due_date')
                            ->label('Jatuh Tempo')
                            ->date('d M Y')
                            ->formatStateUsing(fn ($state) => $state ? $state->format('d M Y') : '-'),
                        
                        TextEntry::make('payment_proof_path')
                            ->label('Bukti Pembayaran')
                            ->formatStateUsing(fn ($state) => $state ? 'File telah diupload' : 'Belum ada bukti pembayaran')
                            ->color(fn ($state) => $state ? 'success' : 'warning')
                            ->url(fn ($record) => $record->payment_proof_path ? asset('storage/' . $record->payment_proof_path) : null, true)
                            ->badge(),
                    ])
                    ->columns(2),

                Section::make('Catatan Internal')
                    ->schema([
                        TextEntry::make('internal_notes')
                            ->label('Catatan Tim Internal')
                            ->formatStateUsing(fn ($state) => $state ?: 'Tidak ada catatan khusus')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record->internal_notes),

                Section::make('Timeline & Tugas Terkait')
                    ->schema([
                        TextEntry::make('tasks')
                            ->label('Tugas yang Dibuat')
                            ->formatStateUsing(function ($record) {
                                // Pastikan relasi tasks ada dan loaded
                                if (!$record->relationLoaded('tasks')) {
                                    $record->load('tasks');
                                }
                                
                                $tasks = $record->tasks;
                                
                                if (!$tasks || $tasks->isEmpty()) {
                                    return 'Belum ada tugas yang dibuat';
                                }
                                
                                return $tasks->map(function ($task) {
                                    $status = match($task->status) {
                                        'todo' => '⏳ Menunggu',
                                        'in_progress' => '🔄 Dikerjakan',
                                        'review' => '👀 Review',
                                        'completed' => '✅ Selesai',
                                        'cancelled' => '❌ Dibatalkan',
                                        default => $task->status
                                    };
                                    return "• {$task->title} ({$status})";
                                })->join("\n");
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            
            Actions\ActionGroup::make([
                Actions\Action::make('mark_paid')
                    ->label('Tandai Lunas')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->action(fn () => $this->record->update(['status' => 'active']))
                    ->visible(fn () => $this->record->status === 'pending_payment')
                    ->requiresConfirmation(),
                
                Actions\Action::make('mark_completed')
                    ->label('Selesaikan Kampanye')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->action(fn () => $this->record->update(['status' => 'completed']))
                    ->visible(fn () => $this->record->status === 'active')
                    ->requiresConfirmation(),
                
                Actions\Action::make('cancel')
                    ->label('Batalkan Kampanye')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(fn () => $this->record->update(['status' => 'cancelled']))
                    ->visible(fn () => !in_array($this->record->status, ['completed', 'cancelled']))
                    ->requiresConfirmation(),
            ])
            ->label('Ubah Status')
            ->icon('heroicon-m-ellipsis-vertical'),
        ];
    }
}