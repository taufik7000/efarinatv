<?php

namespace App\Filament\Marketing\Resources;

use App\Filament\Marketing\Resources\AdTypeResource\Pages;
use App\Models\AdType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AdTypeResource extends Resource
{
    protected static ?string $model = AdType::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Jenis Iklan';
    protected static ?string $pluralModelLabel = 'Jenis Iklan';
    
    // --- PERUBAHAN DI SINI ---
    protected static ?string $navigationGroup = 'Manajemen Penjualan Iklan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Jenis Iklan')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama Jenis Iklan')->searchable(),
                Tables\Columns\TextColumn::make('description')->label('Deskripsi')->limit(50),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdTypes::route('/'),
            'create' => Pages\CreateAdType::route('/create'),
            'edit' => Pages\EditAdType::route('/{record}/edit'),
        ];
    }    
}