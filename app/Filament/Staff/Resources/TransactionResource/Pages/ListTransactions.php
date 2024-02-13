<?php

namespace App\Filament\Staff\Resources\TransactionResource\Pages;

use App\Enums\BorrowedStatus;
use App\Filament\Staff\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'All' => Tab::make(),
            'Borrowed' => Tab::make()
                ->modifyQueryUsing(fn ($query) => $query->whereStatus(BorrowedStatus::Borrowed)),
            'Returned' => Tab::make()
                ->modifyQueryUsing(fn ($query) => $query->whereStatus(BorrowedStatus::Returned)),
            'Delayed' => Tab::make()
                ->modifyQueryUsing(fn ($query) => $query->whereStatus(BorrowedStatus::Delayed)),
        ];
    }
}
