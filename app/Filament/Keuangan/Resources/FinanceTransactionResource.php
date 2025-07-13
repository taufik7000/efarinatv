<?php

namespace App\Filament\Keuangan\Resources;

use App\Filament\Keuangan\Resources\FinanceTransactionResource\Pages;
use App\Models\Advertisement;
use App\Models\FinanceTransaction;
use App\Models\Team;
use App\Models\FinanceCategory;
use App\Models\BudgetType; // Pastikan model ini di-import
use App\Services\TaskHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Collection;
use Filament\Forms\Components\Grid;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Forms\Components\Section as FormSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\IconEntry;
use Illuminate\Support\Facades\Storage;

class FinanceTransactionResource extends Resource
{
    protected static ?string $model = FinanceTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationLabel = 'Kelola Transaksi';
    protected static ?string $pluralModelLabel = 'Transaksi Keuangan';
    protected static ?string $navigationGroup = 'Manajemen Keuangan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FormSection::make('Informasi Utama')
                    ->description('Informasi dasar transaksi')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\Select::make('type')
                                ->label('Tipe Transaksi')
                                ->options(['income' => 'Pemasukan', 'expense' => 'Pengeluaran'])
                                ->required()->native(false)->live()
                                ->helperText('Pilih jenis transaksi'),

                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->options(['pending' => 'Menunggu Persetujuan', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'paid' => 'Telah Dibayar'])
                                ->default('pending')->required()->native(false),
                        ]),
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Jumlah Pemasukan')
                            ->numeric()->prefix('Rp')->required()
                            ->visible(fn (Forms\Get $get) => $get('type') === 'income')
                            ->helperText('Masukkan total pemasukan'),
                        Grid::make(2)->schema([
                            Forms\Components\DateTimePicker::make('transaction_date')->label('Tanggal Transaksi')->default(now())->required(),
                            Forms\Components\Select::make('urgency_level')
                                ->label('Tingkat Urgensi')
                                ->options(['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi', 'urgent' => 'Sangat Urgent'])
                                ->default('medium')->native(false)
                                ->visible(fn (Forms\Get $get) => $get('type') === 'expense'),
                        ]),
                        Forms\Components\TextInput::make('project_name')->label('Nama Proyek/Kegiatan')->placeholder('Nama proyek atau kegiatan terkait')->maxLength(255)->visible(fn (Forms\Get $get) => $get('type') === 'expense'),
                        Forms\Components\Textarea::make('description')->label('Deskripsi Transaksi')->required()->rows(3)->columnSpanFull()->helperText('Jelaskan detail transaksi ini'),
                        Grid::make(2)->schema([
                            // --- AWAL PERUBAHAN ---
                            Forms\Components\Select::make('budget_type_id')
                                ->label('Jenis Anggaran')
                                ->relationship('budgetType', 'name')
                                ->searchable()
                                ->preload()
                                ->native(false)
                                ->visible(fn (Forms\Get $get) => $get('type') === 'expense'),
                            // --- AKHIR PERUBAHAN ---
                            Forms\Components\TextInput::make('pic_contact')->label('PIC/Contact Person')->placeholder('Nama dan kontak PIC'),
                        ]),
                    ])->collapsible(),

                FormSection::make('Rincian Biaya')
                    ->description('Detail breakdown biaya')
                    ->visible(fn (Forms\Get $get) => $get('type') === 'expense')
                    ->schema([
                        Forms\Components\Repeater::make('details')
                            ->label('Rincian Biaya')
                            ->schema([
                                Grid::make(3)->schema([
                                    Forms\Components\Select::make('team_id')->label('Tim')->options(Team::pluck('name', 'id'))->searchable()->required()->native(false),
                                    Forms\Components\Select::make('category_id')->label('Kategori')->options(FinanceCategory::pluck('name', 'id'))->searchable()->required()->native(false),
                                    Forms\Components\TextInput::make('quantity')->label('Qty')->numeric()->default(1)->minValue(1)->live()
                                        ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state) {
                                            $quantity = (float) ($state ?: 1);
                                            $unitPrice = (float) ($get('unit_price') ?: 0);
                                            $set('amount', $quantity * $unitPrice);
                                        }),
                                ]),
                                Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('item_description')->label('Deskripsi Item')->required()->placeholder('Deskripsi detail item'),
                                    Forms\Components\TextInput::make('unit_price')->label('Harga Satuan')->numeric()->prefix('Rp')->live()
                                        ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state) {
                                            $unitPrice = (float) ($state ?: 0);
                                            $quantity = (float) ($get('quantity') ?: 1);
                                            $set('amount', $quantity * $unitPrice);
                                        }),
                                ]),
                                Forms\Components\TextInput::make('amount')->label('Total Harga')->required()->numeric()->prefix('Rp')->readOnly(),
                                Forms\Components\Textarea::make('justification')->label('Justifikasi')->placeholder('Alasan mengapa item ini diperlukan')->columnSpanFull(),
                            ])->addActionLabel('+ Tambah Item')->columns(1)->collapsible()->cloneable()->reorderableWithButtons()
                            ->itemLabel(function (array $state): ?string {
                                $description = $state['item_description'] ?? 'Item baru';
                                $amount = isset($state['amount']) ? 'Rp ' . number_format($state['amount']) : '';
                                return $description . ($amount ? " - {$amount}" : '');
                            })->minItems(1),
                    ])->collapsible(),

                FormSection::make('Bukti Transaksi')
                    ->description('Upload dokumen pendukung')
                    ->schema([
                        Forms\Components\Repeater::make('attachments')->label('Lampiran Bukti')->schema([
                            Forms\Components\FileUpload::make('file_path')->label('Unggah File')->directory('finance-attachments')->acceptedFileTypes(['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'])->maxSize(10240)->required()
                                ->afterStateUpdated(function (Forms\Set $set, $state) {
                                    if ($state) {
                                        $set('file_name', $state->getClientOriginalName());
                                        $set('file_type', $state->getMimeType());
                                        $set('file_size', $state->getSize());
                                        $set('uploaded_by', Auth::id());
                                    }
                                }),
                            Forms\Components\Select::make('document_type')->label('Jenis Dokumen')->options(['invoice' => 'Invoice/Tagihan', 'receipt' => 'Kwitansi/Bukti Bayar', 'quotation' => 'Quotation/Penawaran', 'contract' => 'Kontrak/Perjanjian', 'specification' => 'Spesifikasi', 'other' => 'Lainnya'])->required()->native(false),
                            Forms\Components\TextInput::make('document_description')->label('Deskripsi Dokumen')->placeholder('Jelaskan isi dokumen')->columnSpanFull(),
                            Forms\Components\Hidden::make('file_name'),
                            Forms\Components\Hidden::make('file_type'),
                            Forms\Components\Hidden::make('file_size'),
                            Forms\Components\Hidden::make('uploaded_by'),
                        ])->addActionLabel('+ Tambah Bukti')->collapsible()->itemLabel(fn (array $state): ?string => $state['document_description'] ?? 'Dokumen baru'),
                        Forms\Components\Textarea::make('additional_notes')->label('Catatan Tambahan')->placeholder('Catatan khusus untuk transaksi ini')->rows(3)->columnSpanFull(),
                        Forms\Components\Checkbox::make('is_urgent_request')->label('Transaksi Urgent')->helperText('Centang jika memerlukan persetujuan segera'),
                        Forms\Components\Textarea::make('urgent_reason')->label('Alasan Urgent')->placeholder('Jelaskan mengapa transaksi ini urgent')->visible(fn (Forms\Get $get): bool => $get('is_urgent_request'))->required(fn (Forms\Get $get): bool => $get('is_urgent_request'))->rows(2)->columnSpanFull(),
                    ])->collapsible(),
                
                Forms\Components\Hidden::make('user_id')->default(Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (FinanceTransaction $record): string => static::getUrl('view', ['record' => $record]))
            ->columns([
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipe')
                    ->colors(['success' => 'income', 'danger' => 'expense',])
                    ->icon(fn (string $state): string => match ($state) {
                        'income' => 'heroicon-o-arrow-trending-up',
                        'expense' => 'heroicon-o-arrow-trending-down',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('project_name')->label('Proyek/Kegiatan')->searchable()->limit(25)->tooltip(fn ($record) => $record->project_name)->default('-'),
                Tables\Columns\TextColumn::make('description')->label('Deskripsi')->limit(30)->searchable()->tooltip(fn ($record) => $record->description),
                Tables\Columns\TextColumn::make('total_amount')->label('Total')->money('IDR')->sortable()->summarize([Tables\Columns\Summarizers\Sum::make()->money('IDR')->label('Total'),]),
                Tables\Columns\BadgeColumn::make('urgency_level')->label('Urgensi')->colors(['gray' => 'low', 'info' => 'medium', 'warning' => 'high', 'danger' => 'urgent',])->formatStateUsing(fn (?string $state): string => match ($state) { 'low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi', 'urgent' => 'Urgent', default => '-', })->default('-'),
                Tables\Columns\TextColumn::make('transaction_date')->label('Tanggal')->dateTime('d M Y')->sortable()->color(fn ($record) => $record->transaction_date < now() ? 'danger' : null),
                Tables\Columns\BadgeColumn::make('status')->colors(['warning' => 'pending', 'success' => 'approved', 'danger' => 'rejected', 'info' => 'paid',])->formatStateUsing(fn (string $state): string => match ($state) { 'pending' => 'Pending', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'paid' => 'Lunas', default => ucfirst($state), }),
                Tables\Columns\TextColumn::make('user.name')->label('Diinput Oleh')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('approver.name')->label('Disetujui Oleh')->sortable()->default('-')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_urgent_request')->label('Urgent')->boolean()->trueIcon('heroicon-o-exclamation-triangle')->falseIcon('')->trueColor('danger')->tooltip('Transaksi Urgent'),
                Tables\Columns\TextColumn::make('advertisement.client_name')->label('Klien Iklan')->default('-')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')->label('Tipe Transaksi')->options(['income' => 'Pemasukan', 'expense' => 'Pengeluaran',]),
                SelectFilter::make('status')->options(['pending' => 'Pending', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'paid' => 'Telah Dibayar',]),
                SelectFilter::make('urgency_level')->label('Tingkat Urgensi')->options(['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi', 'urgent' => 'Sangat Urgent',]),
                // --- AWAL PERUBAHAN ---
                SelectFilter::make('budget_type_id')->label('Jenis Anggaran')->relationship('budgetType', 'name')->searchable()->preload(),
                // --- AKHIR PERUBAHAN ---
                Tables\Filters\Filter::make('urgent_only')->label('Hanya Urgent')->query(fn ($query) => $query->where('is_urgent_request', true)),
                Tables\Filters\Filter::make('this_month')->label('Bulan Ini')->query(fn ($query) => $query->whereMonth('created_at', now()->month)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()->visible(fn (FinanceTransaction $record): bool => in_array($record->status, ['pending', 'rejected']) && $record->user_id === Auth::id()),
                    Tables\Actions\DeleteAction::make()->visible(fn (FinanceTransaction $record): bool => in_array($record->status, ['pending', 'rejected']) && $record->user_id === Auth::id())->requiresConfirmation(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('10s');
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFinanceTransactions::route('/'),
            'create' => Pages\CreateFinanceTransaction::route('/create'),
            'edit' => Pages\EditFinanceTransaction::route('/{record}/edit'),
            'view' => Pages\ViewFinanceTransaction::route('/{record}'),
        ];
    }
    
    public static function getNavigationBadge(): ?string { return static::getModel()::where('status', 'pending')->count(); }
    public static function getNavigationBadgeColor(): ?string { $pendingCount = static::getNavigationBadge(); return $pendingCount > 0 ? 'warning' : null; }
    public static function getWidgets(): array { return []; }
}