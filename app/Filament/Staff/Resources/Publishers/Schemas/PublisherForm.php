<?php

namespace App\Filament\Staff\Resources\Publishers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class PublisherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Section::make("Publisher's Profile")
                            ->schema([
                                Group::make([
                                    TextInput::make('name')
                                        ->required(),
                                    DatePicker::make('founded')
                                        ->required(),
                                ])->columns(2),
                            ])->columnSpan(['sm' => 2, 'md' => 2, 'xxl' => 5]),
                        Section::make('Logo')
                            ->schema([
                                Group::make([
                                    SpatieMediaLibraryFileUpload::make('logo')
                                        ->label('')
                                        ->avatar()
                                        ->image()
                                        ->imageEditor()
                                        ->collection('publishers')
                                        ->deleteUploadedFileUsing(function ($file): void {
                                            Storage::disk('public')->delete($file);
                                        })
                                        ->extraAttributes([
                                            'class' => 'justify-center',
                                        ]),
                                ]),
                            ])->columnSpan(['sm' => 2, 'md' => 1, 'xxl' => 1]),
                    ])->columnSpanFull(),
            ]);
    }
}
