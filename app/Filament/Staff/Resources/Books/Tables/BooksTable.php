<?php

namespace App\Filament\Staff\Resources\Books\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class BooksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover_image')
                    ->collection('coverBooks')
                    ->conversion('thumb'),
                TextColumn::make('title')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('author.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('stock'),
                ToggleColumn::make('available'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()
                        ->before(function ($record): void {
                            Storage::disk('public')->delete($record);
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function ($records): void {
                            $records->each(function ($record): void {
                                Storage::disk('public')->delete($record);
                            });
                        }),
                ]),
            ]);
    }
}
