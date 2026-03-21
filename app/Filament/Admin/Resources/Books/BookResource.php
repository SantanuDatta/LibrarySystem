<?php

namespace App\Filament\Admin\Resources\Books;

use App\Filament\Admin\Resources\Books\Pages\CreateBook;
use App\Filament\Admin\Resources\Books\Pages\EditBook;
use App\Filament\Admin\Resources\Books\Pages\ListBooks;
use App\Filament\Admin\Resources\Books\Schemas\BookForm;
use App\Filament\Admin\Resources\Books\Tables\BooksTable;
use App\Http\Traits\NavigationCount;
use App\Models\Book;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BookResource extends Resource
{
    use NavigationCount;

    protected static ?string $model = Book::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static string|\UnitEnum|null $navigationGroup = 'Books & Transactions';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $globalSearchResultLimit = 20;

    /** @return array<string, string|null> */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Author' => $record->author->name ?? null,
            'Publisher' => $record->publisher->name ?? null,
            'Genre' => $record->genre->name ?? null,
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return BookForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BooksTable::configure($table);
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
            'index' => ListBooks::route('/'),
            'create' => CreateBook::route('/create'),
            'edit' => EditBook::route('/{record}/edit'),
        ];
    }
}
