<?php

namespace App\Filament\Redaksi\Resources;

use App\Filament\Redaksi\Resources\PengajuanAnggaranResource\Pages;
use App\Models\FinanceTransaction;
use App\Models\FinanceCategory;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Colors\Color;
use Filament\Forms\Components\Grid;

class PengajuanAnggaranResource extends Resource
{
    protected static ?string $model = FinanceTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-plus';
    protected static ?string $navigationLabel = 'Pengajuan Anggaran';
    protected static ?string $pluralModelLabel = 'Pengajuan Anggaran';
    protected static ?string $navigationGroup = 'Keuangan & Anggaran';
    protected static ?int $navigationSort = 1;

    /**
     * Kueri ini memastikan bahwa anggota tim Redaksi hanya bisa melihat 
     * pengajuan yang mereka buat sendiri.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id())
            ->where('type', 'expense'); // Hanya pengajuan expense
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    
                    // STEP 1: Informasi Dasar Pengajuan
                    Forms\Components\Wizard\Step::make('Informasi Pengajuan')
                        ->description('Informasi dasar tentang pengajuan anggaran')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Grid::make(2)->schema([
                                Forms\Components\Select::make('urgency_level')
                                    ->label('Tingkat Urgensi')
                                    ->options([
                                        'low' => 'Rendah - Dapat ditunda',
                                        'medium' => 'Sedang - Dalam 1-2 minggu',
                                        'high' => 'Tinggi - Segera dibutuhkan',
                                        'urgent' => 'Sangat Urgent - Hari ini/besok'
                                    ])
                                    ->default('medium')
                                    ->required()
                                    ->native(false)
                                    ->live()
                                    ->helperText('Pilih tingkat urgensi sesuai kebutuhan'),

                                Forms\Components\DateTimePicker::make('transaction_date')
                                    ->label('Tanggal Dibutuhkan')
                                    ->default(now()->addDays(3))
                                    ->required()
                                    ->minDate(now())
                                    ->helperText('Kapan anggaran ini dibutuhkan?'),
                            ]),

                            Forms\Components\TextInput::make('project_name')
                                ->label('Nama Proyek/Kegiatan')
                                ->placeholder('Contoh: Liputan Pemilu 2024, Produksi Video Dokumenter')
                                ->required()
                                ->maxLength(255)
                                ->helperText('Nama spesifik proyek atau kegiatan yang membutuhkan anggaran'),

                            Forms\Components\Textarea::make('description')
                                ->label('Deskripsi & Tujuan Pengajuan')
                                ->placeholder('Jelaskan secara detail untuk keperluan apa anggaran ini diajukan, target yang ingin dicapai, dan manfaatnya...')
                                ->required()
                                ->rows(4)
                                ->columnSpanFull()
                                ->helperText('Berikan penjelasan yang detail dan jelas'),

                            Grid::make(2)->schema([
                                Forms\Components\Select::make('budget_type')
                                    ->label('Jenis Anggaran')
                                    ->options([
                                        'operational' => 'Operasional Rutin',
                                        'project' => 'Proyek Khusus',
                                        'equipment' => 'Peralatan & Teknologi',
                                        'travel' => 'Perjalanan Dinas',
                                        'event' => 'Event & Kegiatan',
                                        'emergency' => 'Darurat/Tidak Terduga',
                                        'training' => 'Pelatihan & Pengembangan',
                                        'maintenance' => 'Pemeliharaan'
                                    ])
                                    ->required()
                                    ->native(false),

                                Forms\Components\TextInput::make('expected_completion')
                                    ->label('Estimasi Penyelesaian')
                                    ->placeholder('Contoh: 2 minggu, 1 bulan')
                                    ->helperText('Berapa lama proyek/kegiatan ini akan berjalan?'),
                            ]),
                        ]),

                    // STEP 2: Rincian Kebutuhan yang Lebih Detail
                    Forms\Components\Wizard\Step::make('Rincian Anggaran')
                        ->description('Detail breakdown anggaran yang dibutuhkan')
                        ->icon('heroicon-o-calculator')
                        ->schema([
                            Forms\Components\Repeater::make('details')
                                ->label('Rincian Biaya')
                                ->schema([
                                    Grid::make(3)->schema([
                                        Forms\Components\Select::make('category_id')
                                            ->label('Kategori Pengeluaran')
                                            ->options(FinanceCategory::pluck('name', 'id'))
                                            ->searchable()
                                            ->required()
                                            ->native(false)
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Nama Kategori')
                                                    ->required(),
                                                Forms\Components\Textarea::make('description')
                                                    ->label('Deskripsi'),
                                            ])
                                            ->createOptionUsing(function (array $data): int {
                                                return FinanceCategory::create($data)->getKey();
                                            }),

                                        Forms\Components\TextInput::make('quantity')
                                            ->label('Jumlah/Qty')
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1)
                                            ->live()
                                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state) {
                                                $quantity = (float) ($state ?: 1);
                                                $unitPrice = (float) ($get('unit_price') ?: 0);
                                                $set('amount', $quantity * $unitPrice);
                                            }),

                                        Forms\Components\TextInput::make('unit_price')
                                            ->label('Harga Satuan')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->live()
                                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state) {
                                                $unitPrice = (float) ($state ?: 0);
                                                $quantity = (float) ($get('quantity') ?: 1);
                                                $set('amount', $quantity * $unitPrice);
                                            }),
                                    ]),

                                    Grid::make(2)->schema([
                                        Forms\Components\TextInput::make('item_description')
                                            ->label('Deskripsi Item')
                                            ->placeholder('Contoh: Kamera Sony A7 III untuk dokumentasi')
                                            ->required()
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('amount')
                                            ->label('Total Harga')
                                            ->required()
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->readOnly()
                                            ->columnSpan(1),
                                    ]),

                                    Forms\Components\Textarea::make('justification')
                                        ->label('Justifikasi/Alasan')
                                        ->placeholder('Mengapa item ini diperlukan? Apa dampaknya jika tidak ada?')
                                        ->rows(2)
                                        ->columnSpanFull(),

                                    Forms\Components\Select::make('supplier_vendor')
                                        ->label('Supplier/Vendor (Opsional)')
                                        ->options([
                                            'toko_a' => 'Toko A - Elektronik',
                                            'vendor_b' => 'Vendor B - Catering',
                                            'supplier_c' => 'Supplier C - ATK',
                                            'other' => 'Lainnya'
                                        ])
                                        ->native(false)
                                        ->searchable(),

                                    // Hidden field untuk team_id (akan diset otomatis)
                                    Forms\Components\Hidden::make('team_id'),
                                ])
                                ->addActionLabel('+ Tambah Item Anggaran')
                                ->columns(1)
                                ->collapsible()
                                ->cloneable()
                                ->deleteAction(
                                    fn ($action) => $action->requiresConfirmation()
                                )
                                ->reorderableWithButtons()
                                ->itemLabel(function (array $state): ?string {
                                    $description = $state['item_description'] ?? 'Item baru';
                                    $amount = isset($state['amount']) ? 'Rp ' . number_format($state['amount']) : '';
                                    return $description . ($amount ? " - {$amount}" : '');
                                })
                                ->minItems(1),

                            Forms\Components\Placeholder::make('total_info')
                                ->label('Informasi Total')
                                ->content(function (Forms\Get $get): string {
                                    $details = $get('details') ?? [];
                                    $total = 0;
                                    $itemCount = 0;
                                    
                                    foreach ($details as $detail) {
                                        if (isset($detail['amount']) && is_numeric($detail['amount'])) {
                                            $total += (float) $detail['amount'];
                                            $itemCount++;
                                        }
                                    }
                                    
                                    return "📊 **Total Items:** {$itemCount} | **Total Anggaran:** Rp " . number_format($total);
                                })
                                ->columnSpanFull(),
                        ]),

                    // STEP 3: Informasi Tambahan & Dokumen
                    Forms\Components\Wizard\Step::make('Informasi Pendukung')
                        ->description('Dokumen dan informasi pendukung lainnya')
                        ->icon('heroicon-o-paper-clip')
                        ->schema([
                            Grid::make(2)->schema([
                                Forms\Components\Select::make('approval_needed_by')
                                    ->label('Persetujuan Diperlukan Oleh')
                                    ->options([
                                        'manager' => 'Manager Redaksi',
                                        'finance_manager' => 'Manager Keuangan', 
                                        'director' => 'Direktur',
                                        'board' => 'Dewan Direksi'
                                    ])
                                    ->default('finance_manager')
                                    ->native(false)
                                    ->helperText('Siapa yang perlu menyetujui pengajuan ini?'),

                                Forms\Components\TextInput::make('pic_contact')
                                    ->label('PIC/Kontak Person')
                                    ->placeholder('Nama dan nomor HP PIC proyek')
                                    ->helperText('Contact person yang bisa dihubungi terkait pengajuan ini'),
                            ]),

                            Forms\Components\Textarea::make('additional_notes')
                                ->label('Catatan Tambahan')
                                ->placeholder('Informasi tambahan, referensi harga, alternatif supplier, dll.')
                                ->rows(3)
                                ->columnSpanFull(),

                            Forms\Components\Repeater::make('attachments')
                                ->label('Dokumen Pendukung')
                                ->schema([
                                    Forms\Components\FileUpload::make('file_path')
                                        ->label('Upload File')
                                        ->directory('pengajuan-anggaran')
                                        ->acceptedFileTypes(['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'])
                                        ->maxSize(10240) // 10MB
                                        ->required()
                                        ->afterStateUpdated(function (Forms\Set $set, $state) {
                                            if ($state) {
                                                $set('file_name', $state->getClientOriginalName());
                                                $set('file_type', $state->getMimeType());
                                                $set('file_size', $state->getSize());
                                                $set('uploaded_by', Auth::id());
                                            }
                                        }),

                                    Forms\Components\Select::make('document_type')
                                        ->label('Jenis Dokumen')
                                        ->options([
                                            'quotation' => 'Quotation/Penawaran Harga',
                                            'specification' => 'Spesifikasi Teknis',
                                            'comparison' => 'Perbandingan Harga',
                                            'proposal' => 'Proposal Kegiatan',
                                            'reference' => 'Referensi/Contoh',
                                            'other' => 'Lainnya'
                                        ])
                                        ->required()
                                        ->native(false),

                                    Forms\Components\TextInput::make('document_description')
                                        ->label('Deskripsi Dokumen')
                                        ->placeholder('Jelaskan isi atau tujuan dokumen ini')
                                        ->columnSpanFull(),

                                    // Hidden fields
                                    Forms\Components\Hidden::make('file_name'),
                                    Forms\Components\Hidden::make('file_type'),
                                    Forms\Components\Hidden::make('file_size'),
                                    Forms\Components\Hidden::make('uploaded_by'),
                                ])
                                ->addActionLabel('+ Tambah Dokumen')
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => $state['document_description'] ?? 'Dokumen baru'),

                            Forms\Components\Checkbox::make('is_urgent_request')
                                ->label('Ini adalah permintaan urgent')
                                ->helperText('Centang jika pengajuan ini memerlukan proses persetujuan yang dipercepat'),

                            Forms\Components\Textarea::make('urgent_reason')
                                ->label('Alasan Urgent')
                                ->placeholder('Jelaskan mengapa pengajuan ini urgent dan tidak bisa ditunda...')
                                ->visible(fn (Forms\Get $get): bool => $get('is_urgent_request'))
                                ->required(fn (Forms\Get $get): bool => $get('is_urgent_request'))
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),

                ])
                ->columnSpanFull()
                ->skippable()
                ->persistStepInQueryString(),

                // Hidden fields yang akan diset otomatis
                Forms\Components\Hidden::make('type')->default('expense'),
                Forms\Components\Hidden::make('status')->default('pending'),
                Forms\Components\Hidden::make('user_id')->default(Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project_name')
                    ->label('Nama Proyek')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->project_name),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Anggaran')
                    ->money('IDR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('IDR')
                            ->label('Total Semua'),
                    ]),

                Tables\Columns\BadgeColumn::make('urgency_level')
                    ->label('Urgensi')
                    ->colors([
                        'secondary' => 'low',
                        'warning' => 'medium', 
                        'danger' => 'high',
                        'danger' => 'urgent',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'low' => 'Rendah',
                        'medium' => 'Sedang',
                        'high' => 'Tinggi', 
                        'urgent' => 'Sangat Urgent',
                        default => ucfirst($state),
                    }),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'info' => 'paid',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu Persetujuan',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'paid' => 'Telah Dibayar',
                        default => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Tgl Dibutuhkan')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn ($record) => $record->transaction_date < now() ? 'danger' : null),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl Pengajuan')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_urgent_request')
                    ->label('Urgent')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('')
                    ->trueColor('danger'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Menunggu Persetujuan',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'paid' => 'Telah Dibayar',
                    ]),

                Tables\Filters\SelectFilter::make('urgency_level')
                    ->label('Tingkat Urgensi')
                    ->options([
                        'low' => 'Rendah',
                        'medium' => 'Sedang',
                        'high' => 'Tinggi',
                        'urgent' => 'Sangat Urgent',
                    ]),

                Tables\Filters\SelectFilter::make('budget_type')
                    ->label('Jenis Anggaran')
                    ->options([
                        'operational' => 'Operasional',
                        'project' => 'Proyek Khusus',
                        'equipment' => 'Peralatan',
                        'travel' => 'Perjalanan Dinas',
                        'event' => 'Event',
                        'emergency' => 'Darurat',
                    ]),

                Tables\Filters\Filter::make('urgent_only')
                    ->label('Hanya Urgent')
                    ->query(fn (Builder $query): Builder => $query->where('is_urgent_request', true)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('info'),
                    
                Tables\Actions\EditAction::make()
                    ->visible(fn (Model $record) => in_array($record->status, ['pending', 'rejected']))
                    ->color('warning'),
                    
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Model $record) => in_array($record->status, ['pending', 'rejected']))
                    ->requiresConfirmation(),

                Tables\Actions\Action::make('duplicate')
                    ->label('Duplikasi')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('success')
                    ->action(function (Model $record) {
                        $newRecord = $record->replicate();
                        $newRecord->status = 'pending';
                        $newRecord->project_name = $record->project_name . ' (Copy)';
                        $newRecord->created_at = now();
                        $newRecord->save();
                        
                        // Duplikasi details
                        foreach ($record->details as $detail) {
                            $newDetail = $detail->replicate();
                            $newDetail->transaction_id = $newRecord->id;
                            $newDetail->save();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Duplikasi Pengajuan')
                    ->modalDescription('Apakah Anda yakin ingin menduplikasi pengajuan ini?'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Pengajuan Anggaran')
            ->emptyStateDescription('Buat pengajuan anggaran pertama Anda dengan klik tombol di bawah.')
            ->emptyStateIcon('heroicon-o-document-plus')
            ->defaultSort('created_at', 'desc');
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengajuanAnggarans::route('/'),
            'create' => Pages\CreatePengajuanAnggaran::route('/create'),
            'edit' => Pages\EditPengajuanAnggaran::route('/{record}/edit'),
            'view' => Pages\ViewPengajuanAnggaran::route('/{record}'),
        ];
    }    

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('user_id', auth()->id())
            ->where('status', 'pending')
            ->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getNavigationBadge() > 0 ? 'warning' : null;
    }
}