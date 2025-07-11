<?php

namespace App\Filament\Direktur\Resources;

use App\Filament\Direktur\Resources\FinanceBudgetResource\Pages;
use App\Models\FinanceBudget;
use App\Models\FinanceCategory;
use App\Models\Team;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class FinanceBudgetResource extends Resource
{
    protected static ?string $model = FinanceBudget::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Budgeting';
    protected static ?string $pluralModelLabel = 'Anggaran';

    protected static ?string $navigationGroup = 'Manajemen Keuangan';
    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Anggaran')
                    ->description('Tetapkan alokasi dana untuk setiap tim dan kategori.')
                    ->schema([
                        Forms\Components\Select::make('team_id')
                            ->label('Tim / Departemen')
                            ->options(Team::pluck('name', 'id'))
                            ->searchable()
                            ->native(false)
                            ->placeholder('Pilih Tim (opsional)'),
                        
                        Forms\Components\Select::make('category_id')
                            ->label('Kategori Anggaran')
                            ->options(FinanceCategory::pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->native(false),
                        
                        Forms\Components\TextInput::make('amount')
                            ->label('Jumlah Anggaran')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required(),
                        
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Berakhir')
                            ->required()
                            ->after('start_date'),
                        
                        Forms\Components\Hidden::make('user_id')
                            ->default(Auth::id()),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('team.name')
                    ->label('Tim')
                    ->sortable()
                    ->default('Umum'),
                
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Berakhir')
                    ->date('d M Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Ditetapkan Oleh')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFinanceBudgets::route('/'),
            'create' => Pages\CreateFinanceBudget::route('/create'),
            'edit' => Pages\EditFinanceBudget::route('/{record}/edit'),
        ];
    }    
}