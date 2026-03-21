<?php

namespace App\Filament\Admin\Resources\Publishers\Pages;

use App\Filament\Admin\Resources\Publishers\PublisherResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreatePublisher extends CreateRecord
{
    protected static string $resource = PublisherResource::class;

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
