<?php

namespace App\Filament\Team\Resources;

use App\Filament\Team\Resources\AccountabilityReportResource\Pages;
use App\Models\AccountabilityReport;
use App\Models\FinanceTransaction;
use App\Models\FinanceTransactionDetail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class AccountabilityReportResource extends Resource
{
    protected static ?string $model = AccountabilityReport::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'Laporan Pertanggungjawaban';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Laporan')
                    ->schema([
                        Forms\Components\Placeholder::make('project_name')
                            ->label('Untuk Transaksi Proyek')
                            ->content(fn ($record) => $record->transaction->project_name ?? '-'),
                        Forms\Components\DatePicker::make('report_date')->label('Tanggal Laporan')->required(),
                        Forms\Components\RichEditor::make('summary')->label('Ringkasan Laporan')->required()->columnSpanFull(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Rincian Pertanggungjawaban')
                    ->description('Isi biaya aktual dan unggah bukti untuk setiap item yang diajukan.')
                    ->schema([
                        Forms\Components\Repeater::make('details')
                            ->relationship()
                            ->schema([
                                Forms\Components\Placeholder::make('item_description')
                                    ->label('Item Diajukan')
                                    ->content(function ($get) {
                                        $detail = FinanceTransactionDetail::find($get('finance_transaction_detail_id'));
                                        if (!$detail) return 'Item tidak ditemukan';
                                        return $detail->item_description . ' (Estimasi: Rp ' . number_format($detail->amount) . ')';
                                    }),
                                Forms\Components\TextInput::make('actual_amount')->label('Biaya Aktual')->numeric()->prefix('Rp')->required()->live(),
                                Forms\Components\FileUpload::make('receipt_path')->label('Unggah Bukti')->directory('receipts')->image(),
                                Forms\Components\Textarea::make('notes')->label('Catatan Item')->columnSpanFull(),
                            ])
                            ->addable(false)->deletable(false)->collapsible()->columns(2)
                            ->itemLabel(fn (array $state): ?string => optional(FinanceTransactionDetail::find($state['finance_transaction_detail_id']))->item_description ?? 'Item'),
                    ]),
                
                Forms\Components\Hidden::make('status')->default('submitted'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction.project_name')->label('Proyek'),
                Tables\Columns\TextColumn::make('actual_amount_spent')->label('Dana Terpakai')->money('IDR'),
                
                // --- AWAL PENAMBAHAN ---
                Tables\Columns\TextColumn::make('remaining_funds')
                    ->label('Dana Sisa')
                    ->money('IDR')
                    ->state(function (AccountabilityReport $record): float {
                        // Hitung dana sisa: Total dana diterima - Total dana terpakai
                        $sisa = $record->transaction->total_amount - $record->actual_amount_spent;
                        return $sisa > 0 ? $sisa : 0;
                    })
                    ->color('info')
                    ->weight('bold'),
                // --- AKHIR PENAMBAHAN ---

                Tables\Columns\TextColumn::make('status')->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending_submission' => 'Menunggu Diisi',
                        'submitted' => 'Terkirim',
                        'revision_needed' => 'Perlu Revisi',
                        'approved' => 'Disetujui',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('return_status')->label('Status Pengembalian')->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'awaiting_confirmation' => 'warning',
                        'completed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'awaiting_confirmation' => 'Menunggu Konfirmasi',
                        'completed' => 'Selesai',
                        default => 'Belum ada',
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Isi Laporan'),
                Tables\Actions\ViewAction::make(),
                Action::make('return_funds')
                    ->label('Kembalikan Dana Sisa')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->form([
                        FileUpload::make('return_proof_path')->label('Bukti Pengembalian Dana')->required()->directory('return-proofs'),
                        Textarea::make('return_notes')->label('Catatan Pengembalian'),
                    ])
                    ->action(function (AccountabilityReport $record, array $data) {
                        $sisaDana = $record->transaction->total_amount - $record->actual_amount_spent;
                        FinanceTransaction::create([
                            'type' => 'income',
                            'status' => 'pending',
                            'user_id' => $record->user_id,
                            'total_amount' => $sisaDana,
                            'description' => 'Pengembalian Dana Sisa dari: ' . $record->transaction->project_name,
                            'transaction_date' => now(),
                            'source_accountability_report_id' => $record->id,
                        ]);
                        $record->update([
                            'return_status' => 'awaiting_confirmation',
                            'return_proof_path' => $data['return_proof_path'],
                            'return_notes' => $data['return_notes'],
                        ]);
                        Notification::make()->title('Pengembalian dana berhasil dikirim')->success()->send();
                    })
                    ->visible(function (AccountabilityReport $record): bool {
                        // Tombol muncul jika ada sisa dana, laporan sudah dikirim, dan belum ada proses pengembalian
                        return ($record->transaction->total_amount > $record->actual_amount_spent)
                               && ($record->status === 'submitted') // <-- Diubah dari 'approved' menjadi 'submitted'
                               && (is_null($record->return_status));
                    }),
            ]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountabilityReports::route('/'),
            'view' => Pages\ViewAccountabilityReport::route('/{record}'),
            'edit' => Pages\EditAccountabilityReport::route('/{record}/edit'),
        ];
    }    
}