<?php

namespace App\Filament\Admin\Resources\Transactions;

use App\Filament\Admin\Resources\Transactions\Pages\CreateTransaction;
use App\Filament\Admin\Resources\Transactions\Pages\EditTransaction;
use App\Filament\Admin\Resources\Transactions\Pages\ListTransactions;
use App\Filament\Admin\Resources\Transactions\Schemas\TransactionForm;
use App\Filament\Admin\Resources\Transactions\Tables\TransactionsTable;
use App\Http\Traits\NavigationCount;
use App\Models\Transaction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TransactionResource extends Resource
{
    use NavigationCount;

    protected static ?string $model = Transaction::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static string|\UnitEnum|null $navigationGroup = 'Books & Transactions';

    protected static ?string $recordTitleAttribute = 'user.name';

    protected static ?int $globalSearchResultLimit = 20;

    /**
     * @param  Transaction  $record
     */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Borrower' => $record->user->name ?? null,
            'Book Borrowed' => $record->book->title ?? null,
            'Status' => $record->status,
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return TransactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransactionsTable::configure($table);
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
            'index' => ListTransactions::route('/'),
            'create' => CreateTransaction::route('/create'),
            'edit' => EditTransaction::route('/{record}/edit'),
        ];
    }
}
