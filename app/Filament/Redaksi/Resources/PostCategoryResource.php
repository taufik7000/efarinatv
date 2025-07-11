<?php

namespace App\Filament\Redaksi\Resources;

use App\Filament\Redaksi\Resources\PostCategoryResource\Pages;
use App\Models\PostCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PostCategoryResource extends Resource
{
    protected static ?string $model = PostCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $modelLabel = 'Kategori Berita';
    protected static ?string $pluralModelLabel = 'Kategori Berita';
    
    // Mengelompokkan menu ini di bawah "Manajemen Konten"
    protected static ?string $navigationGroup = 'Manajemen Konten';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Kategori')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state))),
                
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->unique(PostCategory::class, 'slug', ignoreRecord: true),
                
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama Kategori')->searchable(),
                Tables\Columns\TextColumn::make('slug'),
                // Menghitung jumlah post di setiap kategori
                Tables\Columns\TextColumn::make('posts_count')->counts('posts')->label('Jumlah Post'),
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
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPostCategories::route('/'),
            'create' => Pages\CreatePostCategory::route('/create'),
            'edit' => Pages\EditPostCategory::route('/{record}/edit'),
        ];
    }    
}