<?php

namespace App\Filament\Admin\Resources\Publishers\Pages;

use App\Filament\Admin\Resources\Publishers\PublisherResource;
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
