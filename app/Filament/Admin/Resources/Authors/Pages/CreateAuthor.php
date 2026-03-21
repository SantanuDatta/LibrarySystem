<?php

namespace App\Filament\Admin\Resources\Authors\Pages;

use App\Filament\Admin\Resources\Authors\AuthorResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateAuthor extends CreateRecord
{
    protected static string $resource = AuthorResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reset')
                ->outlined()
                ->icon('heroicon-o-arrow-path')
                ->action(fn () => $this->fillForm()),
        ];
    }
}
