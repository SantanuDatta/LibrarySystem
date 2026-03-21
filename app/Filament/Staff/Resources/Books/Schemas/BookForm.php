<?php

namespace App\Filament\Staff\Resources\Books\Schemas;

use App\Models\Author;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class BookForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Section::make([
                            Group::make([
                                TextInput::make('title')
                                    ->required(),
                                Select::make('publisher_id')
                                    ->relationship('publisher', 'name')
                                    ->searchable()
                                    ->native(false)
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(fn (Set $set) => $set('author_id', null))
                                    ->required(),
                                Select::make('author_id')
                                    ->label('Author')
                                    ->options(
                                        fn (Get $get): Collection => Author::query()
                                            ->with('publisher')
                                            ->where('publisher_id', $get('publisher_id'))
                                            ->pluck('name', 'id')
                                    )
                                    ->searchable()
                                    ->native(false)
                                    ->preload()
                                    ->live()
                                    ->required(),
                                Select::make('genre_id')
                                    ->relationship('genre', 'name')
                                    ->searchable()
                                    ->native(false)
                                    ->preload()
                                    ->required(),
                            ])->columns(2),
                            Group::make([
                                TextInput::make('isbn')
                                    ->prefixIcon('heroicon-o-qr-code')
                                    ->prefixIconColor('white')
                                    ->numeric()
                                    ->required()
                                    ->unique(ignoreRecord: true),
                                TextInput::make('price')
                                    ->prefix('$')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->numeric()
                                    ->required(),
                                TextInput::make('stock')
                                    ->prefixIcon('heroicon-o-archive-box')
                                    ->prefixIconColor('white')
                                    ->numeric()
                                    ->required(),
                            ])->columns(3),
                            RichEditor::make('description')
                                ->disableToolbarButtons(['attachFiles'])
                                ->columnSpanFull(),
                        ])->columnSpan(['sm' => 2, 'md' => 2, 'xxl' => 5]),
                        Section::make([
                            Group::make([
                                SpatieMediaLibraryFileUpload::make('cover_image')
                                    ->image()
                                    ->imageEditor()
                                    ->imageEditorAspectRatioOptions([
                                        '1:1.6',
                                    ])
                                    ->collection('coverBooks')
                                    ->responsiveImages(true)
                                    ->deleteUploadedFileUsing(function ($file): void {
                                        Storage::disk('public')->delete($file);
                                    }),
                                DatePicker::make('published')
                                    ->required(),
                                Toggle::make('available'),
                            ]),
                        ])->columnSpan(['sm' => 2, 'md' => 1, 'xxl' => 1]),
                    ])->columnSpanFull(),
            ]);
    }
}
