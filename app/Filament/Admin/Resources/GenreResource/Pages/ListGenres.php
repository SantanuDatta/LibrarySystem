<?php

namespace App\Filament\Admin\Resources\GenreResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Admin\Resources\GenreResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGenres extends ListRecords
{
    protected static string $resource = GenreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
