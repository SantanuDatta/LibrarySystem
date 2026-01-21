<?php

namespace App\Filament\Staff\Resources\BookResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Staff\Resources\BookResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBooks extends ListRecords
{
    protected static string $resource = BookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
