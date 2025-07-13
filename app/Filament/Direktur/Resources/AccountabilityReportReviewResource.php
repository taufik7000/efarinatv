<?php

namespace App\Filament\Direktur\Resources;

use App\Filament\Direktur\Resources\AccountabilityReportReviewResource\Pages;
use App\Models\AccountabilityReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class AccountabilityReportReviewResource extends Resource
{
    protected static ?string $model = AccountabilityReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';
    protected static ?string $navigationLabel = 'Tinjau Pertanggungjawaban';
    protected static ?string $pluralModelLabel = 'Tinjauan Laporan';
    protected static ?string $navigationGroup = 'Laporan';

    public static function canCreate(): bool
    {
        return false; // Direktur tidak membuat laporan, hanya meninjau
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]); // Form tidak diperlukan karena Direktur tidak mengedit
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction.project_name')->label('Proyek')->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('Dibuat Oleh')->searchable(),
                Tables\Columns\TextColumn::make('report_date')->label('Tgl. Laporan')->date('d M Y'),
                Tables\Columns\TextColumn::make('actual_amount_spent')->label('Dana Terpakai')->money('IDR'),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending_submission' => 'gray',
                        'submitted' => 'warning',
                        'revision_needed' => 'danger',
                        'approved' => 'success',
                        default => 'gray',
                    })
                     ->formatStateUsing(fn(string $state): string => match($state) {
                        'pending_submission' => 'Draf',
                        'submitted' => 'Menunggu Tinjauan',
                        'revision_needed' => 'Perlu Revisi',
                        'approved' => 'Disetujui',
                        default => $state,
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountabilityReportReviews::route('/'),
            'view' => Pages\ViewAccountabilityReportReview::route('/{record}'),
        ];
    }
}