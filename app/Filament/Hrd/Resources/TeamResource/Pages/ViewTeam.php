<?php

namespace App\Filament\Hrd\Resources\TeamResource\Pages;

use App\Filament\Hrd\Resources\TeamResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\IconEntry;

class ViewTeam extends ViewRecord
{
    protected static string $resource = TeamResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Tim')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama Tim'),
                        
                        TextEntry::make('department')
                            ->label('Departemen')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'redaksi' => 'blue',
                                'marketing' => 'green',
                                'keuangan' => 'yellow',
                                'hrd' => 'purple',
                                'teknis' => 'orange',
                                'administrasi' => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                        
                        IconEntry::make('is_active')
                            ->label('Status')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),
                        
                        TextEntry::make('teamLeader.name')
                            ->label('Ketua Tim')
                            ->default('-'),
                        
                        TextEntry::make('description')
                            ->label('Deskripsi')
                            ->default('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Anggota Tim')
                    ->schema([
                        RepeatableEntry::make('members')
                            ->label('')
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Nama')
                                    ->icon('heroicon-m-user'),
                                
                                TextEntry::make('email')
                                    ->label('Email')
                                    ->icon('heroicon-m-envelope'),
                                
                                TextEntry::make('roles.0.name')
                                    ->label('Role')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => ucfirst($state ?? 'team')),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record->members && $record->members->isNotEmpty()),

                Section::make('Statistik Tim')
                    ->schema([
                        TextEntry::make('members_count')
                            ->label('Total Anggota')
                            ->formatStateUsing(fn ($record) => $record->members->count()),
                        
                        TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime('d M Y H:i'),
                        
                        TextEntry::make('updated_at')
                            ->label('Terakhir Diupdate')
                            ->dateTime('d M Y H:i'),
                    ])
                    ->columns(3),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}