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
                        TextEntry::make('status_text')->label('Status')->badge(),
                        TextEntry::make('priority_text')->label('Prioritas')->badge(),
                        TextEntry::make('category.name')->label('Kategori'),
                        TextEntry::make('department')->label('Departemen')->badge(),
                        TextEntry::make('due_date')->label('Deadline')->dateTime(),
                        TextEntry::make('description')->label('Deskripsi')->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('Penugasan')
                    ->schema([
                        TextEntry::make('creator.name')->label('Dibuat Oleh'),
                        TextEntry::make('assignee.name')->label('Ditugaskan Kepada')->default('-'),
                        TextEntry::make('assignedTeam.name')->label('Tim')->default('-'),
                        TextEntry::make('advertisement.title')->label('Terkait Iklan')->default('-'),
                        TextEntry::make('created_at')->label('Dibuat Pada')->dateTime(),
                        TextEntry::make('completed_at')
                            ->label('Diselesaikan Pada')
                            ->dateTime()
                            ->formatStateUsing(function ($state) {
                                return $state ? $state->format('d M Y H:i') : '-';
                            }),
                    ])
                    ->columns(3),

                Section::make('Catatan & Lampiran')
                    ->schema([
                        TextEntry::make('notes')
                            ->label('Catatan Tambahan')
                            ->formatStateUsing(fn ($state) => $state ?: '-')
                            ->columnSpanFull(),
                        
                        RepeatableEntry::make('attachments')
                            ->label('Lampiran')
                            ->schema([
                                TextEntry::make('file_name')->label('Nama File'),
                                TextEntry::make('description')
                                    ->label('Keterangan')
                                    ->formatStateUsing(fn ($state) => $state ?: '-'),
                                TextEntry::make('uploadedBy.name')->label('Diupload Oleh'),
                            ])
                            ->columns(3)
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record->attachments->isNotEmpty()),
                    ]),

                Section::make('Komentar')
                    ->schema([
                        RepeatableEntry::make('comments')
                            ->label('')
                            ->schema([
                                TextEntry::make('user.name')->label('Oleh'),
                                TextEntry::make('comment')->label('Komentar')->columnSpanFull(),
                                TextEntry::make('created_at')
                                    ->label('Waktu')
                                    ->formatStateUsing(fn ($state) => $state->format('d M Y H:i')),
                            ])
                            ->columns(2)
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record->comments->isNotEmpty()),
                    ])
                    ->visible(fn ($record) => $record->comments->isNotEmpty()),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}