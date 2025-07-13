<?php

namespace App\Filament\Direktur\Resources;

use App\Filament\Direktur\Resources\FinanceBudgetResource\Pages;
use App\Models\FinanceBudget;
use App\Models\FinanceCategory;
use App\Models\FinanceTransaction;
use App\Models\FinanceTransactionDetail; // <-- Import model ini
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;

class FinanceBudgetResource extends Resource
{
    protected static ?string $model = FinanceBudget::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Budgeting';
    protected static ?string $pluralModelLabel = 'Anggaran';
    protected static ?string $navigationGroup = 'Manajemen Keuangan';

    // Method `form()` tidak ada perubahan...
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Alokasi Anggaran')
                    ->description('Tetapkan alokasi dana untuk setiap Jenis Anggaran.')
                    ->schema([
                        Forms\Components\Select::make('budget_type_id')
                            ->label('Jenis Anggaran')
                            ->relationship('budgetType', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('amount')->label('Jumlah Anggaran')->required()->numeric()->prefix('Rp'),
                        Forms\Components\Select::make('period_type')->label('Jenis Periode')->options(['monthly' => 'Bulanan', 'yearly' => 'Tahunan',])->required()->native(false)->default('monthly'),
                        Forms\Components\Select::make('year')->label('Tahun Anggaran')->options(fn() => array_combine(range(now()->year, now()->year + 5), range(now()->year, now()->year + 5)))->required()->default(now()->year),
                        Forms\Components\Toggle::make('is_active')->label('Status Anggaran')->default(true)->columnSpanFull(),
                        Forms\Components\Hidden::make('user_id')->default(Auth::id()),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (FinanceBudget $record): string => static::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('year')->label('Tahun')->sortable(),
                TextColumn::make('budgetType.name')->label('Jenis Anggaran')->searchable()->sortable(),
                TextColumn::make('amount')->label('Alokasi Budget')->money('IDR')->sortable(),

                // --- AWAL PERBAIKAN LOGIKA ---
                TextColumn::make('used_budget')
                    ->label('Terpakai')
                    ->money('IDR')
                    ->state(function (FinanceBudget $record): float {
                        // 1. Dapatkan semua ID kategori di bawah jenis budget ini.
                        $categoryIds = FinanceCategory::where('budget_type_id', $record->budget_type_id)->pluck('id');
                        
                        // Jika tidak ada kategori, tidak ada yang terpakai.
                        if ($categoryIds->isEmpty()) {
                            return 0;
                        }

                        // 2. Dapatkan ID transaksi unik dari detail yang relevan.
                        $transactionIds = FinanceTransactionDetail::whereIn('category_id', $categoryIds)
                            ->pluck('transaction_id')
                            ->unique();
                        
                        // Jika tidak ada transaksi, tidak ada yang terpakai.
                        if ($transactionIds->isEmpty()) {
                            return 0;
                        }

                        // 3. Hitung total dari transaksi yang valid.
                        return FinanceTransaction::whereIn('id', $transactionIds)
                            ->whereYear('transaction_date', $record->year)
                            ->whereIn('status', ['approved', 'paid'])
                            ->sum('total_amount');
                    })
                    ->color('danger'),

                TextColumn::make('remaining_budget')
                    ->label('Sisa')
                    ->money('IDR')
                    ->state(function (FinanceBudget $record): float {
                        // Logika ini menggunakan perhitungan dari kolom 'used_budget' untuk efisiensi.
                        $categoryIds = FinanceCategory::where('budget_type_id', $record->budget_type_id)->pluck('id');
                        if ($categoryIds->isEmpty()) {
                            return $record->amount;
                        }
                        $transactionIds = FinanceTransactionDetail::whereIn('category_id', $categoryIds)->pluck('transaction_id')->unique();
                        if ($transactionIds->isEmpty()) {
                            return $record->amount;
                        }
                        $used = FinanceTransaction::whereIn('id', $transactionIds)
                            ->whereYear('transaction_date', $record->year)
                            ->whereIn('status', ['approved', 'paid'])
                            ->sum('total_amount');
                        return $record->amount - $used;
                    })
                    ->color('success'),
                // --- AKHIR PERBAIKAN LOGIKA ---
                
                Tables\Columns\IconColumn::make('is_active')->label('Status')->boolean(),
            ])
            ->defaultSort('year', 'desc')
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
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFinanceBudgets::route('/'),
            'create' => Pages\CreateFinanceBudget::route('/create'),
            'edit' => Pages\EditFinanceBudget::route('/{record}/edit'),
            'view' => Pages\ViewFinanceBudget::route('/{record}'),
        ];
    }    
}