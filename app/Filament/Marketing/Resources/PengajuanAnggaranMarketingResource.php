<?php

namespace App\Filament\Marketing\Resources;

use App\Filament\Marketing\Resources\PengajuanAnggaranMarketingResource\Pages;
use App\Models\FinanceTransaction;
use App\Models\FinanceCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PengajuanAnggaranMarketingResource extends Resource
{
    protected static ?string $model = FinanceTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-plus';
    protected static ?string $navigationLabel = 'Pengajuan Anggaran';
    protected static ?string $pluralModelLabel = 'Pengajuan Anggaran';
    protected static ?string $navigationGroup = 'Operasional & Pengeluaran';

    public static function getEloquentQuery(): Builder
    {
        // --- PERUBAHAN DI SINI ---
        // Kita hanya akan memfilter berdasarkan tipe 'expense' untuk saat ini
        // agar semua data pengajuan muncul, tidak peduli siapa yang membuatnya.
        return parent::getEloquentQuery()
            ->where('type', 'expense');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Informasi Pengajuan')
                        ->schema([
                            Forms\Components\DateTimePicker::make('transaction_date')->label('Tanggal Dibutuhkan')->default(now())->required(),
                            Forms\Components\Textarea::make('description')->label('Tujuan Pengajuan Anggaran')->required()->columnSpanFull(),
                        ]),
                    Forms\Components\Wizard\Step::make('Rincian Kebutuhan')
                        ->schema([
                            Forms\Components\Repeater::make('details')
                                ->label('Rincian')
                                ->schema([
                                    Forms\Components\Select::make('category_id')->label('Kategori Pengeluaran')->options(FinanceCategory::pluck('name', 'id'))->searchable()->required()->native(false),
                                    Forms\Components\TextInput::make('amount')->label('Jumlah Diajukan')->required()->numeric()->prefix('Rp'),
                                ])->addActionLabel('Tambah Rincian')->columns(2)->collapsible(),
                        ]),
                    Forms\Components\Wizard\Step::make('Dokumen Pendukung')
                        ->schema([
                            Forms\Components\Repeater::make('attachments')
                                ->label('Lampiran (Opsional)')
                                ->schema([
                                    Forms\Components\FileUpload::make('file_path')->label('Unggah File')->directory('attachments')->required(),
                                ])->addActionLabel('Tambah Lampiran'),
                        ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('description')->label('Tujuan Pengajuan')->limit(40)->searchable(),
                Tables\Columns\TextColumn::make('total_amount')->label('Total Diajukan')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('transaction_date')->label('Tanggal Diajukan')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) { 'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', 'paid' => 'info', default => 'gray', })->formatStateUsing(fn (string $state): string => ucfirst($state)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->visible(fn (Model $record) => $record->status === 'pending' || $record->status === 'rejected'),
                Tables\Actions\DeleteAction::make()->visible(fn (Model $record) => $record->status === 'pending' || $record->status === 'rejected'),
            ]);
    }
    
    public static function getPages(): array
    {
        // --- PERUBAHAN DI SINI ---
        // Memastikan nama halaman sinkron dengan nama file yang kita buat
        return [
            'index' => Pages\ListPengajuanAnggaranMarketings::route('/'),
            'create' => Pages\CreatePengajuanAnggaranMarketing::route('/create'),
            'edit' => Pages\EditPengajuanAnggaranMarketing::route('/{record}/edit'),
            'view' => Pages\ViewPengajuanAnggaranMarketing::route('/{record}'),
        ];
    }    
}