<?php

namespace App\Filament\Redaksi\Resources;

use App\Filament\Redaksi\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\FileUpload; 
use Filament\Tables\Columns\ImageColumn;  

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $pluralModelLabel = 'Manajemen Berita';
    protected static ?string $modelLabel = 'Berita';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()->columns(3)->schema([
                    // --- KOLOM KIRI (KONTEN UTAMA) ---
                    Forms\Components\Section::make('Konten')
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->label('Judul Berita')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state))),

                            Forms\Components\TextInput::make('slug')
                                ->label('Slug (URL)')
                                ->required()
                                ->unique(Post::class, 'slug', ignoreRecord: true),

                            Select::make('category_id')
                                ->label('Kategori')
                                ->relationship('category', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),

                            Select::make('tags')
                                ->label('Tags')
                                ->relationship('tags', 'name')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state)))
                                        ->required(),
                                    Forms\Components\TextInput::make('slug')->required(),
                                ])
                                ->required(),

                            Forms\Components\RichEditor::make('content')
                                ->label('Isi Berita')
                                ->required()
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->columnSpan(2),

                    // --- KOLOM KANAN (METADATA & PUBLIKASI) ---
                    Forms\Components\Section::make('Publikasi')
                        ->schema([
                            FileUpload::make('thumbnail')
                                ->label('Gambar Thumbnail')
                                ->image()
                                ->directory('thumbnails')
                                ->imageEditor(), // Tetap gunakan imageEditor jika Anda ingin fitur crop/resize

                            // Tambahkan field untuk Alt Text
                            Forms\Components\TextInput::make('thumbnail_alt')
                                ->label('Alt Text Gambar')
                                ->helperText('Teks alternatif untuk gambar (penting untuk SEO & aksesibilitas).')
                                ->maxLength(255)
                                ->nullable(), // Bisa kosong jika tidak diisi

                            // Tambahkan field untuk Caption
                            Forms\Components\TextInput::make('thumbnail_caption')
                                ->label('Caption Gambar')
                                ->helperText('Keterangan singkat untuk gambar.')
                                ->maxLength(255)
                                ->nullable(), // Bisa kosong jika tidak diisi
                            
                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->options([
                                    'draft' => 'Draft',
                                    'pending_approval' => 'Pending Approval',
                                    'published' => 'Published',
                                    'archived' => 'Archived',
                                ])
                                ->default('draft')
                                ->required(),

                            Forms\Components\DateTimePicker::make('published_at')
                                ->label('Tanggal Publikasi')
                                ->default(now()),

                            Select::make('user_id')
                                ->label('Penulis')
                                ->relationship('author', 'name')
                                ->searchable()
                                ->required(),
                        ])
                        ->columnSpan(1),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('Thumbnail'),
                
                Tables\Columns\TextColumn::make('title')->label('Judul')->searchable()->sortable()->limit(40),
                Tables\Columns\TextColumn::make('category.name')->label('Kategori')->sortable(),
                Tables\Columns\TextColumn::make('author.name')->label('Penulis')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'draft' => 'gray',
                    'pending_approval' => 'warning',
                    'published' => 'success',
                    'archived' => 'danger',
                }),
                Tables\Columns\TextColumn::make('published_at')->label('Diterbitkan')->dateTime('d M Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending_approval' => 'Pending Approval',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (Post $record) {
                        $record->status = 'draft';
                        $record->save();
                    })
                    ->visible(fn (Post $record): bool => $record->status === 'pending_approval')
                    ->requiresConfirmation(),
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
        return [];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }    
}
