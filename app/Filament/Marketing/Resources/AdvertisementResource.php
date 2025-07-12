<?php

namespace App\Filament\Marketing\Resources;

use App\Filament\Marketing\Resources\AdvertisementResource\Pages;
use App\Models\Advertisement;
use App\Models\AdType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class AdvertisementResource extends Resource
{
    protected static ?string $model = Advertisement::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationLabel = 'Manajemen Iklan';
    protected static ?string $pluralModelLabel = 'Iklan';

    // --- PERUBAHAN DI SINI ---
    protected static ?string $navigationGroup = 'Manajemen Penjualan Iklan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('client_name')->label('Nama Klien')->required(),
                Forms\Components\TextInput::make('title')->label('Judul Kampanye')->required(),
                Forms\Components\Select::make('ad_type_id')->label('Jenis Iklan')->options(AdType::pluck('name', 'id'))->required()->native(false),
                Forms\Components\TextInput::make('price')->label('Biaya Iklan')->numeric()->prefix('Rp')->required(),
                Forms\Components\DateTimePicker::make('start_date')->label('Tanggal Mulai Tayang')->required(),
                Forms\Components\DateTimePicker::make('end_date')->label('Tanggal Selesai Tayang')->required()->after('start_date'),
                Forms\Components\FileUpload::make('payment_proof_path')
                    ->label('Bukti Pembayaran Iklan')
                    ->directory('payment-proofs')
                    ->columnSpanFull(),
                Forms\Components\Hidden::make('ad_code')->default(fn () => 'IKL-' . strtoupper(Str::random(8))),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ad_code')->label('Kode Iklan')->searchable(),
                Tables\Columns\TextColumn::make('client_name')->label('Klien')->searchable(),
                Tables\Columns\TextColumn::make('title')->label('Kampanye'),
                Tables\Columns\TextColumn::make('price')->label('Biaya')->money('IDR'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending_payment' => 'warning',
                        'active' => 'success',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => 
                        str_replace('_', ' ', Str::title($state))
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdvertisements::route('/'),
            'create' => Pages\CreateAdvertisement::route('/create'),
            'edit' => Pages\EditAdvertisement::route('/{record}/edit'),
            'view' => Pages\ViewAdvertisement::route('/{record}'),
        ];
    }    
}