<?php

namespace App\Filament\Redaksi\Resources;

use App\Filament\Redaksi\Resources\PengajuanAnggaranResource\Pages;
use App\Models\FinanceTransaction;
use App\Models\FinanceCategory;
use App\Models\Team;
use App\Models\User; // <-- Tambahkan ini
use App\Models\BudgetType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Grid;
use Filament\Forms\Get;

class PengajuanAnggaranResource extends Resource
{
    protected static ?string $model = FinanceTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-plus';
    protected static ?string $navigationLabel = 'Pengajuan Anggaran';
    protected static ?string $pluralModelLabel = 'Pengajuan Anggaran';
    protected static ?string $navigationGroup = 'Keuangan & Anggaran';
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id())
            ->where('type', 'expense');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Informasi Pengajuan')
                        ->description('Informasi dasar tentang pengajuan anggaran')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Grid::make(2)->schema([
                                Forms\Components\Select::make('urgency_level')
                                    ->label('Tingkat Urgensi')
                                    ->options(['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi', 'urgent' => 'Sangat Urgent'])
                                    ->default('medium')->required()->native(false)->live(),
                                Forms\Components\DateTimePicker::make('transaction_date')
                                    ->label('Tanggal Dibutuhkan')->default(now()->addDays(3))->required()->minDate(now()),
                            ]),
                            Forms\Components\TextInput::make('project_name')->label('Nama Proyek/Kegiatan')->required()->maxLength(255),
                            Forms\Components\Textarea::make('description')->label('Deskripsi & Tujuan Pengajuan')->required()->rows(4)->columnSpanFull(),
                            Grid::make(2)->schema([
                                Forms\Components\Select::make('budget_type_id')
                                    ->label('Jenis Anggaran')
                                    ->relationship('budgetType', 'name')
                                    ->searchable()->preload()->live()->required(),
                                Forms\Components\TextInput::make('expected_completion')->label('Estimasi Penyelesaian'),
                            ]),
                        ]),

                    Forms\Components\Wizard\Step::make('Rincian Anggaran')
                        ->description('Detail breakdown anggaran yang dibutuhkan')
                        ->icon('heroicon-o-calculator')
                        ->schema([
                            Forms\Components\Repeater::make('details')
                                ->label('Rincian Biaya')
                                ->relationship()
                                ->schema([
                                    Grid::make(2)->schema([
                                        Forms\Components\Select::make('category_id')
                                            ->label('Kategori Pengeluaran')
                                            ->options(function (Get $get) {
                                                $budgetTypeId = $get('../../budget_type_id');
                                                if ($budgetTypeId) {
                                                    return FinanceCategory::where('budget_type_id', $budgetTypeId)->pluck('name', 'id');
                                                }
                                                return FinanceCategory::pluck('name', 'id');
                                            })
                                            ->searchable()->required()->native(false),
                                        Forms\Components\TextInput::make('item_description')->label('Deskripsi Item')->required(),
                                    ]),
                                    Grid::make(3)->schema([
                                        Forms\Components\TextInput::make('quantity')->label('Qty')->numeric()->default(1)->minValue(1)->live()->afterStateUpdated(fn (Forms\Set $set, Get $get, ?string $state) => $set('amount', (float)($state ?: 1) * (float)($get('unit_price') ?: 0))),
                                        Forms\Components\TextInput::make('unit_price')->label('Harga Satuan')->numeric()->prefix('Rp')->live()->afterStateUpdated(fn (Forms\Set $set, Get $get, ?string $state) => $set('amount', (float)($get('quantity') ?: 1) * (float)($state ?: 0))),
                                        Forms\Components\TextInput::make('amount')->label('Total Harga')->required()->numeric()->prefix('Rp')->readOnly(),
                                    ]),
                                    Forms\Components\Textarea::make('justification')->label('Justifikasi/Alasan')->rows(2)->columnSpanFull(),
                                    Forms\Components\Hidden::make('team_id')->default(fn () => Team::where('name', 'Redaksi')->first()?->id),
                                ])
                                ->addActionLabel('+ Tambah Item Anggaran')->columns(1)->collapsible()->cloneable()->reorderableWithButtons()
                                ->itemLabel(fn (array $state): ?string => ($state['item_description'] ?? 'Item baru') . (isset($state['amount']) ? " - Rp " . number_format($state['amount']) : ''))
                                ->minItems(1),
                        ]),

                    Forms\Components\Wizard\Step::make('Informasi Pendukung')
                        ->description('Dokumen dan informasi pendukung lainnya')
                        ->icon('heroicon-o-paper-clip')
                        ->schema([
                            // --- AWAL PERUBAHAN ---
                            Forms\Components\Select::make('pic_user_id')
                                ->label('Penanggung Jawab')
                                ->options(
                                    User::whereHas('roles', fn ($query) => $query->whereIn('name', ['team', 'redaksi']))
                                        ->pluck('name', 'id')
                                )
                                ->searchable()
                                ->preload()
                                ->helperText('Pilih penanggung jawab untuk kegiatan ini.'),
                            // --- AKHIR PERUBAHAN ---
                            Forms\Components\Textarea::make('additional_notes')->label('Catatan Tambahan')->rows(3)->columnSpanFull(),
                            Forms\Components\Repeater::make('attachments')->label('Dokumen Pendukung')->relationship()->schema([
                                Forms\Components\FileUpload::make('file_path')->label('Upload File')->directory('pengajuan-anggaran')->maxSize(10240)->required()->afterStateUpdated(fn (Forms\Set $set, $state) => $state ? ($set('file_name', $state->getClientOriginalName()) & $set('file_type', $state->getMimeType()) & $set('file_size', $state->getSize()) & $set('uploaded_by', Auth::id())) : null),
                                Forms\Components\Select::make('document_type')->label('Jenis Dokumen')->options(['quotation' => 'Quotation', 'specification' => 'Spesifikasi', 'comparison' => 'Perbandingan', 'proposal' => 'Proposal', 'reference' => 'Referensi', 'other' => 'Lainnya'])->required()->native(false),
                                Forms\Components\TextInput::make('document_description')->label('Deskripsi Dokumen')->columnSpanFull(),
                                Forms\Components\Hidden::make('file_name'), Forms\Components\Hidden::make('file_type'), Forms\Components\Hidden::make('file_size'), Forms\Components\Hidden::make('uploaded_by'),
                            ])->addActionLabel('+ Tambah Dokumen')->collapsible()->itemLabel(fn (array $state): ?string => $state['document_description'] ?? 'Dokumen baru'),
                            Forms\Components\Checkbox::make('is_urgent_request')->label('Ini adalah permintaan urgent'),
                            Forms\Components\Textarea::make('urgent_reason')->label('Alasan Urgent')->visible(fn (Get $get): bool => $get('is_urgent_request'))->required(fn (Get $get): bool => $get('is_urgent_request'))->rows(3)->columnSpanFull(),
                        ]),
                ])
                ->columnSpanFull()->skippable()->persistStepInQueryString(),
                Forms\Components\Hidden::make('type')->default('expense'),
                Forms\Components\Hidden::make('status')->default('pending'),
                Forms\Components\Hidden::make('user_id')->default(Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project_name')->label('Nama Proyek')->searchable()->sortable()->limit(30)->tooltip(fn ($record) => $record->project_name),
                Tables\Columns\TextColumn::make('total_amount')->label('Total Anggaran')->money('IDR')->sortable()->summarize([Tables\Columns\Summarizers\Sum::make()->money('IDR')->label('Total Semua'),]),
                Tables\Columns\BadgeColumn::make('urgency_level')->label('Urgensi')->colors(['secondary' => 'low', 'warning' => 'medium', 'danger' => 'high', 'danger' => 'urgent',])->formatStateUsing(fn (string $state): string => match ($state) { 'low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi', 'urgent' => 'Sangat Urgent', default => ucfirst($state), }),
                Tables\Columns\BadgeColumn::make('status')->colors(['warning' => 'pending', 'success' => 'approved', 'danger' => 'rejected', 'info' => 'paid',])->formatStateUsing(fn (string $state): string => match ($state) { 'pending' => 'Menunggu Persetujuan', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'paid' => 'Telah Dibayar', default => ucfirst($state), }),
                Tables\Columns\TextColumn::make('transaction_date')->label('Tgl Dibutuhkan')->date('d M Y')->sortable()->color(fn ($record) => $record->transaction_date < now() ? 'danger' : null),
                Tables\Columns\TextColumn::make('created_at')->label('Tgl Pengajuan')->date('d M Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_urgent_request')->label('Urgent')->boolean()->trueIcon('heroicon-o-exclamation-triangle')->falseIcon('')->trueColor('danger'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(['pending' => 'Menunggu Persetujuan', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'paid' => 'Telah Dibayar',]),
                Tables\Filters\SelectFilter::make('urgency_level')->label('Tingkat Urgensi')->options(['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi', 'urgent' => 'Sangat Urgent',]),
                Tables\Filters\SelectFilter::make('budget_type_id')->label('Jenis Anggaran')->relationship('budgetType', 'name'),
                Tables\Filters\Filter::make('urgent_only')->label('Hanya Urgent')->query(fn (Builder $query): Builder => $query->where('is_urgent_request', true)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->color('info'),
                Tables\Actions\EditAction::make()->visible(fn (Model $record) => in_array($record->status, ['pending', 'rejected']))->color('warning'),
                Tables\Actions\DeleteAction::make()->visible(fn (Model $record) => in_array($record->status, ['pending', 'rejected']))->requiresConfirmation(),
                Tables\Actions\Action::make('duplicate')->label('Duplikasi')->icon('heroicon-o-document-duplicate')->color('success')->action(function (Model $record) {
                    $newRecord = $record->replicate();
                    $newRecord->status = 'pending';
                    $newRecord->project_name = $record->project_name . ' (Copy)';
                    $newRecord->created_at = now();
                    $newRecord->save();
                    foreach ($record->details as $detail) {
                        $newDetail = $detail->replicate();
                        $newDetail->transaction_id = $newRecord->id;
                        $newDetail->save();
                    }
                })->requiresConfirmation()->modalHeading('Duplikasi Pengajuan')->modalDescription('Apakah Anda yakin ingin menduplikasi pengajuan ini?'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()->requiresConfirmation(),]),
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
        return static::getModel()::where('user_id', auth()->id())->where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getNavigationBadge() > 0 ? 'warning' : null;
    }
}