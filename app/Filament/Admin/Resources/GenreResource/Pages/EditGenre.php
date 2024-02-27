<?php

namespace App\Filament\Admin\Resources\GenreResource\Pages;

use App\Filament\Admin\Resources\GenreResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGenre extends EditRecord
{
    protected static string $resource = GenreResource::class;

    protected function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            Action::make('reset')
                ->outlined()
                ->icon('heroicon-o-arrow-path')
                ->action(fn () => $this->fillForm()),
        ];
    }
}
