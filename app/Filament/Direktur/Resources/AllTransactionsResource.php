<?php

namespace App\Filament\Direktur\Resources;

use App\Filament\Direktur\Resources\AllTransactionsResource\Pages;
use App\Models\FinanceTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Filters\SelectFilter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model; // <-- TAMBAHKAN INI

class AllTransactionsResource extends Resource
{
    protected static ?string $model = FinanceTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Semua Transaksi';
    protected static ?string $pluralModelLabel = 'Semua Transaksi';
    protected static ?string $navigationGroup = 'Laporan';

    public static function canCreate(): bool
    {
        return false;
    }

    // --- AWAL PERUBAHAN ---
    public static function canEdit(Model $record): bool // Ubah tipe dari FinanceTransaction menjadi Model
    {
        return false;
    }
    // --- AKHIR PERUBAHAN ---

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')->label('Tipe')->badge()->color(fn (string $state): string => match ($state) { 'income' => 'success', 'expense' => 'danger', })->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('description')->label('Deskripsi')->limit(30)->searchable(),
                Tables\Columns\TextColumn::make('total_amount')->label('Total')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('transaction_date')->label('Tanggal')->dateTime('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) { 'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', 'paid' => 'info', default => 'gray', })->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('user.name')->label('Dibuat Oleh')->sortable(),
                Tables\Columns\TextColumn::make('approver.name')->label('Disetujui Oleh')->sortable()->default('-'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'paid' => 'Telah Dibayar',
                    ]),
                SelectFilter::make('type')
                    ->label('Tipe Transaksi')
                    ->options([
                        'income' => 'Pemasukan',
                        'expense' => 'Pengeluaran',
                    ])
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->infolist(function (Infolist $infolist): Infolist {
                        return $infolist
                            ->schema([
                                Section::make('Informasi Utama')
                                    ->columns(3)
                                    ->schema([
                                        TextEntry::make('type')->label('Tipe Transaksi')->badge()->color(fn ($state) => $state === 'income' ? 'success' : 'danger'),
                                        TextEntry::make('status_text')->label('Status')->badge(),
                                        TextEntry::make('total_amount')->label('Jumlah Total')->money('IDR'),
                                        TextEntry::make('transaction_date')->label('Tanggal Transaksi')->dateTime(),
                                        TextEntry::make('user.name')->label('Diinput Oleh'),
                                        TextEntry::make('approver.name')
                                            ->label('Disetujui Oleh')
                                            ->default('-'),
                                        TextEntry::make('approved_at')
                                            ->label('Tanggal Disetujui')
                                            ->formatStateUsing(function ($state): ?string {
                                                if ($state instanceof Carbon) {
                                                    return $state->isoFormat('D MMMM YYYY, HH:mm');
                                                }
                                                return null;
                                            })
                                            ->default('-'),
                                        TextEntry::make('description')->label('Deskripsi')->columnSpanFull(),
                                    ]),
                                Section::make('Rincian Biaya')
                                    ->hidden(fn ($record) => $record->type !== 'expense')
                                    ->schema([
                                        RepeatableEntry::make('details')
                                            ->label('')
                                            ->schema([
                                                TextEntry::make('team.name')->label('Tim'),
                                                TextEntry::make('category.name')->label('Kategori'),
                                                TextEntry::make('amount')->label('Jumlah')->money('IDR'),
                                            ])->columns(3),
                                    ]),
                                Section::make('Bukti Transaksi')
                                    ->schema([
                                        RepeatableEntry::make('attachments')
                                            ->label('')
                                            ->schema([
                                                TextEntry::make('file_name')
                                                    ->label('Nama File')
                                                    ->url(fn ($record) => Storage::url($record->file_path), true),
                                                TextEntry::make('file_size')
                                                    ->label('Ukuran')
                                                    ->formatStateUsing(fn (int $state): string => round($state / 1024, 2) . ' KB'),
                                                TextEntry::make('uploadedBy.name')->label('Diupload Oleh'),
                                            ])->columns(3),
                                    ]),
                            ]);
                    }),
            ])
            ->bulkActions([]);
    }
    
    public static function getRelations(): array
    {
        return [];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAllTransactions::route('/'),
        ];
    }    
}