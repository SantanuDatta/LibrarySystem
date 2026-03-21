<?php

namespace App\Filament\Staff\Resources\Authors\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class AuthorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Section::make("Author's Profile")
                            ->schema([
                                Group::make([
                                    TextInput::make('name')
                                        ->required(),
                                    Select::make('publisher_id')
                                        ->relationship('publisher', 'name')
                                        ->native(false)
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                    DatePicker::make('date_of_birth')
                                        ->required(),
                                    RichEditor::make('bio')
                                        ->columnSpanFull()
                                        ->disableToolbarButtons([
                                            'attachFiles',
                                        ])->columnSpanFull(),
                                ])->columns(3),
                            ])->columnSpan(['sm' => 2, 'md' => 2, 'xxl' => 5]),
                        Section::make('Avatar')
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('avatar')
                                    ->label('')
                                    ->image()
                                    ->avatar()
                                    ->imageEditor()
                                    ->circleCropper()
                                    ->responsiveImages(true)
                                    ->collection('avatars')
                                    ->deleteUploadedFileUsing(function ($file): void {
                                        Storage::disk('public')->delete($file);
                                    })
                                    ->extraAttributes([
                                        'class' => 'justify-center',
                                    ]),
                            ])->columnSpan(['sm' => 2, 'md' => 1, 'xxl' => 1]),
                    ])->columnSpanFull(),
            ]);
    }
}
