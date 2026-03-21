<?php

namespace App\Filament\Staff\Resources\Transactions\Schemas;

use App\Enums\BorrowedStatus;
use App\Models\Book;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Section::make([
                            Group::make([
                                Select::make('user_id')
                                    ->options(fn () => User::whereStatus(true)
                                        ->whereRelation('role', 'name', 'borrower')
                                        ->pluck('name', 'id'))
                                    ->native(false)
                                    ->searchable()
                                    ->preload()
                                    ->label('Borrower')
                                    ->required(),
                                Select::make('book_id')
                                    ->options(fn () => Book::whereAvailable(true)
                                        ->pluck('title', 'id'))
                                    ->native(false)
                                    ->searchable()
                                    ->preload()
                                    ->label('Book')
                                    ->required(),
                                DatePicker::make('borrowed_date')
                                    ->live()
                                    ->required(),
                                TextInput::make('borrowed_for')
                                    ->suffix('Days')
                                    ->numeric()
                                    ->live()
                                    ->minValue(0)
                                    ->maxValue(30)
                                    ->required(),
                                DatePicker::make('returned_date')
                                    ->visible(fn (Get $get): bool => $get('status') === 'returned'
                                        || $get('status') === 'delayed')
                                    ->afterOrEqual('borrowed_date')
                                    ->live()
                                    ->required(fn (string $context) => $context === 'edit')
                                    ->columnSpanFull(),
                            ])->columns(2),
                        ])->columnSpan(['sm' => 2, 'md' => 2, 'xxl' => 5]),
                        Section::make([
                            Group::make([
                                ToggleButtons::make('status')
                                    ->options(
                                        fn (string $operation) => $operation === 'create'
                                        ? [BorrowedStatus::Borrowed->value => BorrowedStatus::Borrowed->getLabel()]
                                        : BorrowedStatus::class
                                    )
                                    ->default(BorrowedStatus::Borrowed)
                                    ->inline()
                                    ->live(),
                                Group::make()
                                    ->schema([
                                        TextEntry::make('fine')
                                            ->label('$10 Per Day After Delay')
                                            ->state(
                                                function (Get $get): string {
                                                    $borrowedDate = $get('borrowed_date');
                                                    $borrowedFor = $get('borrowed_for');
                                                    $returnedDate = $get('returned_date');
                                                    $borrowedDate = Carbon::parse($borrowedDate);
                                                    $returnedDate = Carbon::parse($returnedDate);
                                                    $dueDate = $borrowedDate->copy()->addDays($borrowedFor);
                                                    $delay = 0;
                                                    $fine = 0;
                                                    if ($returnedDate->gt($dueDate)) {
                                                        $delay = $dueDate->diffInDays($returnedDate);
                                                        $fine = $delay * 10;
                                                    }

                                                    return $delay.' Days x $10 = $'.number_format($fine, 2);
                                                }
                                            )
                                            ->live()
                                            ->visible(fn (Get $get) => $get('returned_date')
                                                && $get('status') === 'delayed'),
                                    ])->visibleOn('edit'),
                            ]),
                        ])->columnSpan(['sm' => 2, 'md' => 1, 'xxl' => 1]),
                    ])->columnSpanFull(),
            ]);
    }
}
