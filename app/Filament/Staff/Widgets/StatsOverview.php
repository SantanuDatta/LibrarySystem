<?php

namespace App\Filament\Staff\Widgets;

use App\Enums\BorrowedStatus;
use App\Models\Book;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '300s';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Books', Book::count())
                ->description('Increase In New Books')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([6, 7, 3, 8, 5, 8]),
            Stat::make('Recent Borrowers', Transaction::whereStatus(BorrowedStatus::Borrowed)
                ->count())
                ->description('Increase In Recent Borrowers')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([6, 7, 3, 8, 5, 8]),
            Stat::make('Delayed Borrowers', Transaction::whereStatus(BorrowedStatus::Delayed)
                ->count())
                ->description('Increase In Delayed Borrowers')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([6, 7, 3, 8, 5, 8]),
        ];
    }
}
