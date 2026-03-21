<?php

namespace App\Filament\Admin\Resources\Authors;

use App\Filament\Admin\Resources\Authors\Pages\CreateAuthor;
use App\Filament\Admin\Resources\Authors\Pages\EditAuthor;
use App\Filament\Admin\Resources\Authors\Pages\ListAuthors;
use App\Filament\Admin\Resources\Authors\RelationManagers\BooksRelationManager;
use App\Filament\Admin\Resources\Authors\Schemas\AuthorForm;
use App\Filament\Admin\Resources\Authors\Tables\AuthorsTable;
use App\Http\Traits\NavigationCount;
use App\Models\Author;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AuthorResource extends Resource
{
    use NavigationCount;

    protected static ?string $model = Author::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static string|\UnitEnum|null $navigationGroup = 'Books & Transactions';

    protected static ?string $recordTitleAttribute = 'name';

    protected static int $globalSearchResultLimit = 20;

    /** @return array<string, string|null> */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Publisher' => $record->publisher->name ?? null,
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('publisher');
    }

    public static function form(Schema $schema): Schema
    {
        return AuthorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AuthorsTable::configure($table);
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
            'index' => ListAuthors::route('/'),
            'create' => CreateAuthor::route('/create'),
            'edit' => EditAuthor::route('/{record}/edit'),
        ];
    }
}
