<?php

namespace App\Filament\Redaksi\Resources;

use App\Filament\Redaksi\Resources\EditorialTaskResource\Pages;
use App\Models\EditorialTask;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EditorialTaskResource extends Resource
{
    protected static ?string $model = EditorialTask::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Daftar Tugas Iklan';
    protected static ?string $pluralModelLabel = 'Tugas Iklan';

    public static function canCreate(): bool
    {
        return false; // Tugas dibuat otomatis, tidak bisa manual
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Form hanya untuk mengubah status
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'Dikerjakan',
                        'completed' => 'Selesai',
                    ])->required(),
                Forms\Components\Select::make('assigned_to_user_id')
                    ->relationship('assignedTo', 'name')
                    ->label('Ditugaskan Kepada'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('advertisement.title')->label('Terkait Iklan')->searchable(),
                Tables\Columns\TextColumn::make('description')->label('Deskripsi Tugas'),
                Tables\Columns\TextColumn::make('due_date')->label('Batas Waktu')->date('d M Y'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('assignedTo.name')->label('Penanggung Jawab')->default('Belum ada'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Update Tugas'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEditorialTasks::route('/'),
            'edit' => Pages\EditEditorialTask::route('/{record}/edit'),
        ];
    }
}
