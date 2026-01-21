<?php

namespace App\Filament\Admin\Resources\TransactionResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Tabs\Tab;
use App\Enums\BorrowedStatus;
use App\Filament\Admin\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
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
