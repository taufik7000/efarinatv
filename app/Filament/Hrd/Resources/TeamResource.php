<?php

namespace App\Filament\Hrd\Resources;

use App\Filament\Hrd\Resources\TeamResource\Pages;
use App\Models\Team;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Manajemen Tim';
    protected static ?string $pluralModelLabel = 'Tim & Departemen';
    protected static ?string $navigationGroup = 'Manajemen SDM';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Tim')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Tim atau Departemen')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Anggota Tim')
                    ->schema([
                        Forms\Components\Select::make('team_members')
                            ->label('Anggota Tim')
                            ->multiple()
                            ->options(function () {
                                return User::whereHas('roles', function ($query) {
                                    $query->where('name', 'team');
                                })->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->helperText('Pilih karyawan dengan role "team" untuk menjadi anggota tim ini.')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Pengaturan Tim')
                    ->schema([
                        Forms\Components\Select::make('team_leader_id')
                            ->label('Ketua Tim')
                            ->options(function (Forms\Get $get) {
                                $selectedMembers = $get('team_members') ?? [];
                                if (empty($selectedMembers)) {
                                    return [];
                                }
                                return User::whereIn('id', $selectedMembers)->pluck('name', 'id');
                            })
                            ->searchable()
                            ->helperText('Pilih ketua tim dari anggota yang sudah dipilih.')
                            ->live(),

                        Forms\Components\Select::make('department')
                            ->label('Departemen Induk')
                            ->options([
                                'redaksi' => 'Redaksi',
                                'marketing' => 'Marketing', 
                                'keuangan' => 'Keuangan',
                                'hrd' => 'HRD',
                                'teknis' => 'Teknis',
                                'administrasi' => 'Administrasi',
                            ])
                            ->helperText('Departemen dimana tim ini berada.'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Tim Aktif')
                            ->default(true)
                            ->helperText('Nonaktifkan jika tim sedang tidak beroperasi.'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Tim')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('department')
                    ->label('Departemen')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'redaksi' => 'blue',
                        'marketing' => 'green',
                        'keuangan' => 'yellow',
                        'hrd' => 'purple',
                        'teknis' => 'orange',
                        'administrasi' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                
                Tables\Columns\TextColumn::make('teamLeader.name')
                    ->label('Ketua Tim')
                    ->default('-'),
                
                Tables\Columns\TextColumn::make('members_count')
                    ->label('Jumlah Anggota')
                    ->counts('members')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department')
                    ->options([
                        'redaksi' => 'Redaksi',
                        'marketing' => 'Marketing',
                        'keuangan' => 'Keuangan',
                        'hrd' => 'HRD',
                        'teknis' => 'Teknis',
                        'administrasi' => 'Administrasi',
                    ]),
                
                Tables\Filters\Filter::make('is_active')
                    ->query(fn ($query) => $query->where('is_active', true))
                    ->label('Tim Aktif')
                    ->default(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListTeams::route('/'),
            'create' => Pages\CreateTeam::route('/create'),
            'edit' => Pages\EditTeam::route('/{record}/edit'),
            'view' => Pages\ViewTeam::route('/{record}'),
        ];
    }    
}