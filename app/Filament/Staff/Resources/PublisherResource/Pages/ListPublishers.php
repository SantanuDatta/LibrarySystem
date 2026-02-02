<?php

namespace App\Filament\Staff\Resources\PublisherResource\Pages;

use App\Filament\Staff\Resources\PublisherResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPublishers extends ListRecords
{
    protected static string $resource = PublisherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
