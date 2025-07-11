<?php

namespace App\Filament\Keuangan\Resources;

use App\Filament\Keuangan\Resources\FinanceTransactionResource\Pages;
use App\Models\Advertisement;
use App\Models\FinanceTransaction;
use App\Models\Team;
use App\Models\FinanceCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class FinanceTransactionResource extends Resource
{
    protected static ?string $model = FinanceTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationLabel = 'Input Transaksi';
    protected static ?string $pluralModelLabel = 'Transaksi Keuangan';

    public static function form(Form $form): Form
    {
        // Kode form tidak perlu diubah, biarkan seperti sebelumnya
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Informasi Utama')
                        ->schema([
                            Forms\Components\Select::make('type')
                                ->label('Tipe Transaksi')
                                ->options(['income' => 'Pemasukan', 'expense' => 'Pengeluaran'])
                                ->required()->native(false)->live(),
                            Forms\Components\TextInput::make('total_amount')
                                ->label('Jumlah Pemasukan')
                                ->numeric()->prefix('Rp')->required()
                                ->visible(fn (Forms\Get $get) => $get('type') === 'income'),
                            Forms\Components\DateTimePicker::make('transaction_date')
                                ->label('Tanggal Transaksi')
                                ->default(now())->required(),
                            Forms\Components\Textarea::make('description')
                                ->label('Deskripsi Utama')
                                ->required()->columnSpanFull(),
                        ])->columns(2),
                    
                    Forms\Components\Wizard\Step::make('Rincian Biaya')
                        ->schema([
                            Forms\Components\Repeater::make('details')
                                ->label('Rincian')
                                ->schema([
                                    Forms\Components\Select::make('team_id')->label('Tim')->options(Team::pluck('name', 'id'))->searchable()->required()->native(false),
                                    Forms\Components\Select::make('category_id')->label('Kategori')->options(FinanceCategory::pluck('name', 'id'))->searchable()->required()->native(false),
                                    Forms\Components\TextInput::make('amount')->label('Jumlah')->required()->numeric()->prefix('Rp'),
                                ])->addActionLabel('Tambah Rincian Biaya')->columns(3)->collapsible(),
                        ])->visible(fn (Forms\Get $get) => $get('type') === 'expense'),

                    Forms\Components\Wizard\Step::make('Bukti Transaksi')
                        ->schema([
                            Forms\Components\Repeater::make('attachments')
                                ->label('Lampiran Bukti')
                                ->schema([
                                    Forms\Components\FileUpload::make('file_path')->label('Unggah File')->directory('attachments')->required()
                                        ->afterStateUpdated(function (Forms\Set $set, $state) {
                                            if ($state) {
                                                $set('file_name', $state->getClientOriginalName());
                                                $set('file_type', $state->getMimeType());
                                                $set('file_size', $state->getSize());
                                                $set('uploaded_by', Auth::id());
                                            }
                                        }),
                                    Forms\Components\Hidden::make('file_name'),
                                    Forms\Components\Hidden::make('file_type'),
                                    Forms\Components\Hidden::make('file_size'),
                                    Forms\Components\Hidden::make('uploaded_by'),
                                ])->addActionLabel('Tambah Bukti')->collapsible(),
                        ]),
                ])
                ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')->label('Tipe')->badge()->color(fn (string $state): string => match ($state) { 'income' => 'success', 'expense' => 'danger', })->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('description')->label('Deskripsi')->limit(30)->searchable(),
                Tables\Columns\TextColumn::make('total_amount')->label('Total')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('transaction_date')->label('Tanggal')->dateTime('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) { 'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', 'paid' => 'info', default => 'gray', })->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('user.name')->label('Dibuat Oleh')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'paid' => 'Telah Dibayar',
                    ])
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (FinanceTransaction $record): bool => !in_array($record->status, ['approved', 'paid'])),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('approve')
                        ->label('Setujui')
                        ->color('success')
                        ->icon('heroicon-o-check-circle')
                        ->action(function (FinanceTransaction $record) {
                            // 1. Update status transaksi
                            $record->update([
                                'status' => 'approved',
                                'approved_by' => Auth::id(),
                                'approved_at' => now(),
                            ]);

                            // 2. Cari iklan terkait dan update statusnya menjadi 'active'
                            if ($record->advertisement_id) {
                                $advertisement = Advertisement::find($record->advertisement_id);
                                if ($advertisement) {
                                    $advertisement->update(['status' => 'active']);
                                }
                            }
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\Action::make('reject')
                        ->label('Tolak')
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->action(function (FinanceTransaction $record) {
                            $record->update([
                                'status' => 'rejected',
                            ]);
                        })
                        ->requiresConfirmation(),
                ])
                ->label('Persetujuan')
                ->icon('heroicon-m-ellipsis-vertical')
                ->visible(fn (FinanceTransaction $record): bool => $record->status === 'pending'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function (Collection $records) {
                            $records->filter(fn ($record) => !in_array($record->status, ['approved', 'paid']))
                                    ->each->delete();
                        }),
                ]),
            ]);
    }
    
    public static function getRelations(): array { return []; }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFinanceTransactions::route('/'),
            'create' => Pages\CreateFinanceTransaction::route('/create'),
            'edit' => Pages\EditFinanceTransaction::route('/{record}/edit'),
        ];
    }    
}