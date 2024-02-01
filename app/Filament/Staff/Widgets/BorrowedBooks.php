<?php

namespace App\Filament\Staff\Widgets;

use App\Filament\Staff\Resources\TransactionResource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class BorrowedBooks extends BaseWidget
{
    protected static ?string $heading = 'Recent Borrowed Books';

    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->query(TransactionResource::getEloquentQuery())
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('book.title')
                    ->searchable()
                    ->label('Borrowed Book'),
                TextColumn::make('borrowed_date')
                    ->date('d M, Y'),
                TextColumn::make('status')
                    ->badge(),
            ]);
    }
}
