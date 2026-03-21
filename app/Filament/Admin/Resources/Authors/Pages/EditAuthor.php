<?php

namespace App\Filament\Admin\Resources\Authors\Pages;

use App\Filament\Admin\Resources\Authors\AuthorResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditAuthor extends EditRecord
{
    protected static string $resource = AuthorResource::class;

    protected function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function ($record): void {
                    Storage::disk('public')
                        ->delete($record);
                }),
            Action::make('reset')
                ->outlined()
                ->icon('heroicon-o-arrow-path')
                ->action(fn () => $this->fillForm()),
        ];
    }
}
