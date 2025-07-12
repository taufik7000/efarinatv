<?php

namespace App\Filament\Marketing\Resources;

use App\Filament\Marketing\Resources\TaskResource\Pages;
use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\Team;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Manajemen Tugas';
    protected static ?string $pluralModelLabel = 'Tugas';
    protected static ?string $navigationGroup = 'Operasional & Pengeluaran';

    /**
     * Query untuk menampilkan task yang relevan untuk marketing
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function ($query) {
                $query->where('department', 'marketing')
                      ->orWhere('assigned_to', auth()->id())
                      ->orWhere('created_by', auth()->id());
            });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Tugas')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Tugas')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi Tugas')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Select::make('category_id')
                            ->label('Kategori')
                            ->options(TaskCategory::pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('priority')
                            ->label('Prioritas')
                            ->options([
                                'low' => 'Rendah',
                                'normal' => 'Normal',
                                'high' => 'Tinggi',
                                'urgent' => 'Mendesak',
                            ])
                            ->default('normal')
                            ->required(),

                        Forms\Components\DateTimePicker::make('due_date')
                            ->label('Deadline')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Penugasan')
                    ->schema([
                        Forms\Components\Select::make('department')
                            ->label('Departemen Tujuan')
                            ->options([
                                'redaksi' => 'Redaksi',
                                'marketing' => 'Marketing',
                                'keuangan' => 'Keuangan',
                                'hrd' => 'HRD',
                                'direktur' => 'Direktur',
                            ])
                            ->default('marketing')
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('assigned_to')
                            ->label('Ditugaskan Kepada')
                            ->options(function (Forms\Get $get) {
                                $department = $get('department');
                                if (!$department) return [];
                                
                                return User::whereHas('roles', function ($query) use ($department) {
                                    $query->where('name', $department);
                                })->pluck('name', 'id');
                            })
                            ->searchable(),

                        Forms\Components\Hidden::make('created_by')
                            ->default(Auth::id()),

                        Forms\Components\Hidden::make('status')
                            ->default('todo'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detail Tambahan')
                    ->schema([
                        Forms\Components\Select::make('advertisement_id')
                            ->label('Terkait Iklan (Opsional)')
                            ->relationship('advertisement', 'title')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Tambahan')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Tugas')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori'),

                Tables\Columns\TextColumn::make('department')
                    ->label('Departemen')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'marketing' => 'green',
                        'redaksi' => 'blue',
                        'keuangan' => 'yellow',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioritas')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'urgent' => 'red',
                        'high' => 'orange',
                        'normal' => 'blue',
                        'low' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'low' => 'Rendah',
                        'normal' => 'Normal',
                        'high' => 'Tinggi',
                        'urgent' => 'Mendesak',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'todo' => 'gray',
                        'in_progress' => 'warning',
                        'review' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'todo' => 'Menunggu',
                        'in_progress' => 'Dikerjakan',
                        'review' => 'Review',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('assignee.name')
                    ->label('Penanggung Jawab')
                    ->default('-'),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Deadline')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->color(fn ($record) => $record->due_date < now() && in_array($record->status, ['todo', 'in_progress']) ? 'danger' : null),

                Tables\Columns\TextColumn::make('advertisement.client_name')
                    ->label('Klien Iklan')
                    ->default('-')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'todo' => 'Menunggu',
                        'in_progress' => 'Dikerjakan',
                        'review' => 'Review',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),
                
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Rendah',
                        'normal' => 'Normal',
                        'high' => 'Tinggi',
                        'urgent' => 'Mendesak',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('start')
                        ->label('Mulai')
                        ->icon('heroicon-o-play')
                        ->color('warning')
                        ->action(fn (Task $record) => $record->update([
                            'status' => 'in_progress',
                            'assigned_to' => Auth::id()
                        ]))
                        ->visible(fn (Task $record) => $record->status === 'todo'),

                    Tables\Actions\Action::make('complete')
                        ->label('Selesai')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (Task $record) => $record->update([
                            'status' => 'completed',
                            'completed_at' => now()
                        ]))
                        ->visible(fn (Task $record) => in_array($record->status, ['in_progress', 'review'])),
                ]),

                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
            'view' => Pages\ViewTask::route('/{record}'),
        ];
    }
}