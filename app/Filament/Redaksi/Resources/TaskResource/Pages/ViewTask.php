<?php

namespace App\Filament\Redaksi\Resources\TaskResource\Pages;

use App\Filament\Redaksi\Resources\TaskResource;
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

                Section::make('Diskusi & Komentar')
                    ->schema([
                        RepeatableEntry::make('comments')
                            ->label('')
                            ->schema([
                                TextEntry::make('user.name')
                                    ->label('Oleh')
                                    ->formatStateUsing(fn ($state) => $state ?: 'User tidak diketahui')
                                    ->icon('heroicon-m-user-circle')
                                    ->iconColor('primary'),
                                
                                TextEntry::make('created_at')
                                    ->label('Waktu')
                                    ->formatStateUsing(function ($state) {
                                        return $state ? $state->diffForHumans() : '-';
                                    })
                                    ->color('gray'),
                                
                                TextEntry::make('comment')
                                    ->label('Komentar')
                                    ->columnSpanFull()
                                    ->formatStateUsing(fn ($state) => nl2br(e($state)))
                                    ->html(),
                            ])
                            ->columns(2)
                            ->columnSpanFull()
                            ->separator(),
                    ])
                    ->visible(fn ($record) => $record->comments && $record->comments->isNotEmpty())
                    ->collapsible(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            
            Actions\Action::make('add_comment')
                ->label('Tambah Komentar')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('info')
                ->form([
                    Forms\Components\Textarea::make('comment')
                        ->label('Komentar Anda')
                        ->required()
                        ->rows(4)
                        ->placeholder('Tulis komentar, saran, atau pertanyaan...')
                        ->helperText('Komentar akan terlihat oleh semua anggota tim.'),
                ])
                ->action(function (array $data): void {
                    $this->record->comments()->create([
                        'comment' => $data['comment'],
                        'user_id' => Auth::id(),
                    ]);
                    
                    // Refresh halaman untuk menampilkan komentar baru
                    $this->redirect(request()->header('Referer'));
                })
                ->modalHeading('Tambah Komentar Baru')
                ->modalSubmitActionLabel('Kirim Komentar'),
            
            Actions\ActionGroup::make([
                Actions\Action::make('start_task')
                    ->label('Mulai Kerjakan')
                    ->icon('heroicon-o-play')
                    ->color('warning')
                    ->action(function (): void {
                        $this->record->update([
                            'status' => 'in_progress',
                            'assigned_to' => Auth::id()
                        ]);
                    })
                    ->visible(fn () => $this->record->status === 'todo')
                    ->requiresConfirmation(),
                
                Actions\Action::make('complete_task')
                    ->label('Selesaikan Tugas')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (): void {
                        $this->record->update([
                            'status' => 'completed',
                            'completed_at' => now()
                        ]);
                    })
                    ->visible(fn () => in_array($this->record->status, ['in_progress', 'review']))
                    ->requiresConfirmation(),
                
                Actions\Action::make('request_review')
                    ->label('Minta Review')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->action(function (): void {
                        $this->record->update(['status' => 'review']);
                    })
                    ->visible(fn () => $this->record->status === 'in_progress')
                    ->requiresConfirmation(),
            ])
            ->label('Ubah Status')
            ->icon('heroicon-m-ellipsis-vertical')
            ->visible(fn () => 
                $this->record->department === 'redaksi' || 
                $this->record->assigned_to === Auth::id() ||
                $this->record->created_by === Auth::id()
            ),
        ];
    }
}