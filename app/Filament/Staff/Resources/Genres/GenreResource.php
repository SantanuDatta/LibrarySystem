<?php

namespace App\Filament\Staff\Resources\Genres;

use App\Filament\Staff\Resources\Genres\Pages\CreateGenre;
use App\Filament\Staff\Resources\Genres\Pages\EditGenre;
use App\Filament\Staff\Resources\Genres\Pages\ListGenres;
use App\Filament\Staff\Resources\Genres\RelationManagers\BooksRelationManager;
use App\Filament\Staff\Resources\Genres\Schemas\GenreForm;
use App\Filament\Staff\Resources\Genres\Tables\GenresTable;
use App\Models\Genre;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class GenreResource extends Resource
{
    protected static ?string $model = Genre::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static string|\UnitEnum|null $navigationGroup = 'Books & Transactions';

    public static function form(Schema $schema): Schema
    {
        return GenreForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GenresTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            BooksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGenres::route('/'),
            'create' => CreateGenre::route('/create'),
            'edit' => EditGenre::route('/{record}/edit'),
        ];
    }
}
