<?php

namespace App\Filament\Direktur\Resources;

use App\Filament\Direktur\Resources\AllTransactionsResource\Pages;
use App\Models\FinanceTransaction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Model;

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

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (FinanceTransaction $record): string => static::getUrl('view', ['record' => $record]))
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
                SelectFilter::make('status')->options(['pending' => 'Pending', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'paid' => 'Telah Dibayar',]),
                SelectFilter::make('type')->label('Tipe Transaksi')->options(['income' => 'Pemasukan', 'expense' => 'Pengeluaran',])
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton(),
            ])
            ->bulkActions([]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAllTransactions::route('/'),
            'view' => Pages\ViewAllTransactions::route('/{record}'),
        ];
    }    
}