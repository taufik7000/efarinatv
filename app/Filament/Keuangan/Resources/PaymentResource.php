<?php

namespace App\Filament\Keuangan\Resources;

use App\Filament\Keuangan\Resources\PaymentResource\Pages;
use App\Models\Payment;
use App\Models\FinanceTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Riwayat Pembayaran';
    protected static ?string $pluralModelLabel = 'Pembayaran';
    protected static ?string $navigationGroup = 'Manajemen Keuangan';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // --- AWAL PERBAIKAN ---
                // Menampilkan nama proyek sebagai teks biasa, bukan inputan.
                Forms\Components\Placeholder::make('transaction_info')
                    ->label('Transaksi yang Akan Dibayar')
                    ->content(function ($get) {
                        $transactionId = request()->get('finance_transaction_id');
                        if ($transactionId) {
                            $transaction = FinanceTransaction::find($transactionId);
                            return $transaction ? $transaction->project_name . ' (' . $transaction->description . ')' : 'Transaksi tidak ditemukan.';
                        }
                        return 'Pilih transaksi dari halaman sebelumnya.';
                    }),

                // Menyimpan ID transaksi secara tersembunyi.
                Forms\Components\Hidden::make('finance_transaction_id')
                    ->default(fn () => request()->get('finance_transaction_id'))
                    ->required(),
                // --- AKHIR PERBAIKAN ---
                
                Forms\Components\DatePicker::make('payment_date')
                    ->label('Tanggal Pembayaran')
                    ->required()
                    ->default(now()),
                
                Forms\Components\Select::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->options([
                        'transfer' => 'Transfer Bank',
                        'cash' => 'Tunai',
                        'e-wallet' => 'E-Wallet',
                    ])
                    ->required(),
                
                Forms\Components\TextInput::make('reference_number')
                    ->label('Nomor Referensi/Kuitansi')
                    ->maxLength(255),
                
                Forms\Components\FileUpload::make('proof_path')
                    ->label('Unggah Bukti Pembayaran')
                    ->directory('payment-proofs')
                    ->required(),
                
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan Pembayaran')
                    ->columnSpanFull(),

                Forms\Components\Hidden::make('user_id')
                    ->default(Auth::id()),
            ]);
    }

    // Method table() tidak ada perubahan
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction.project_name')
                    ->label('Proyek/Kegiatan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_date')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction.total_amount')
                    ->label('Jumlah')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('payment_method')
                     ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('-', ' ', $state)))
                    ->badge(),
                Tables\Columns\TextColumn::make('processor.name')
                    ->label('Diproses Oleh')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
            'view' => Pages\ViewPayment::route('/{record}'),
        ];
    }    
}