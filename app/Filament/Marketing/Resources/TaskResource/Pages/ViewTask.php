<?php 

namespace App\Filament\Marketing\Resources\TaskResource\Pages;

use App\Filament\Marketing\Resources\TaskResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Actions;

class ViewTask extends ViewRecord
{
    protected static string $resource = TaskResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Tugas')
                    ->schema([
                        TextEntry::make('title')->label('Judul'),
                        
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'todo' => 'gray',
                                'in_progress' => 'warning',
                                'review' => 'info',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'todo' => 'Menunggu',
                                'in_progress' => 'Dikerjakan',
                                'review' => 'Review',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                                default => $state,
                            }),
                        
                        TextEntry::make('priority')
                            ->label('Prioritas')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'low' => 'gray',
                                'normal' => 'blue',
                                'high' => 'orange',
                                'urgent' => 'red',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'low' => 'Rendah',
                                'normal' => 'Normal',
                                'high' => 'Tinggi',
                                'urgent' => 'Mendesak',
                                default => $state,
                            }),
                        
                        TextEntry::make('category.name')
                            ->label('Kategori')
                            ->formatStateUsing(fn ($state) => $state ?: '-'),
                        
                        TextEntry::make('department')
                            ->label('Departemen')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'redaksi' => 'Redaksi',
                                'marketing' => 'Marketing',
                                'keuangan' => 'Keuangan',
                                'hrd' => 'HRD',
                                'direktur' => 'Direktur',
                                default => $state,
                            }),
                        
                        TextEntry::make('due_date')
                            ->label('Deadline')
                            ->formatStateUsing(function ($state) {
                                if (!$state) return '-';
                                return $state->format('d M Y H:i');
                            }),
                        
                        TextEntry::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('Penugasan')
                    ->schema([
                        TextEntry::make('creator.name')
                            ->label('Dibuat Oleh')
                            ->formatStateUsing(fn ($state) => $state ?: '-'),
                        
                        TextEntry::make('assignee.name')
                            ->label('Ditugaskan Kepada')
                            ->formatStateUsing(fn ($state) => $state ?: '-'),
                        
                        TextEntry::make('assignedTeam.name')
                            ->label('Tim')
                            ->formatStateUsing(fn ($state) => $state ?: '-'),
                        
                        TextEntry::make('advertisement.title')
                            ->label('Terkait Iklan')
                            ->formatStateUsing(fn ($state) => $state ?: '-'),
                        
                        TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->formatStateUsing(function ($state) {
                                return $state ? $state->format('d M Y H:i') : '-';
                            }),
                        
                        TextEntry::make('completed_at')
                            ->label('Diselesaikan Pada')
                            ->formatStateUsing(function ($state) {
                                return $state ? $state->format('d M Y H:i') : '-';
                            }),
                    ])
                    ->columns(3),

                Section::make('Catatan & Lampiran')
                    ->schema([
                        TextEntry::make('notes')
                            ->label('Catatan Tambahan')
                            ->formatStateUsing(fn ($state) => $state ?: 'Tidak ada catatan tambahan')
                            ->columnSpanFull(),
                        
                        RepeatableEntry::make('attachments')
                            ->label('Lampiran')
                            ->schema([
                                TextEntry::make('file_name')
                                    ->label('Nama File')
                                    ->url(fn ($record) => $record->file_path ? asset('storage/' . $record->file_path) : null, true),
                                
                                TextEntry::make('description')
                                    ->label('Keterangan')
                                    ->formatStateUsing(fn ($state) => $state ?: '-'),
                                
                                TextEntry::make('uploadedBy.name')
                                    ->label('Diupload Oleh')
                                    ->formatStateUsing(fn ($state) => $state ?: '-'),
                            ])
                            ->columns(3)
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record->attachments && $record->attachments->isNotEmpty()),
                    ]),

                Section::make('Komentar')
                    ->schema([
                        RepeatableEntry::make('comments')
                            ->label('')
                            ->schema([
                                TextEntry::make('user.name')
                                    ->label('Oleh')
                                    ->formatStateUsing(fn ($state) => $state ?: 'User tidak diketahui'),
                                
                                TextEntry::make('comment')
                                    ->label('Komentar')
                                    ->columnSpanFull(),
                                
                                TextEntry::make('created_at')
                                    ->label('Waktu')
                                    ->formatStateUsing(function ($state) {
                                        return $state ? $state->format('d M Y H:i') : '-';
                                    }),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record->comments && $record->comments->isNotEmpty()),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}