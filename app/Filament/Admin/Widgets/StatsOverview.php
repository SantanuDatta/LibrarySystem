<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Book;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '300s';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Staff', User::whereRelation('role', 'name', 'staff')
                ->count())
                ->description('Increase In Staff')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([6, 7, 3, 8, 5, 8]),
            Stat::make('Total Borrowers', User::whereRelation('role', 'name', 'borrower')
                ->count())
                ->description('Increase In Borrowers')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([6, 7, 3, 8, 5, 8]),
            Stat::make('Total Books', Book::count())
                ->description('Increase In Books')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([6, 7, 3, 8, 5, 8]),
        ];
    }
}
