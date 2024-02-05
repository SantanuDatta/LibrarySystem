<?php

namespace App\Filament\Staff\Resources\GenreResource\RelationManagers;

use App\Models\Author;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\RawJs;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class BooksRelationManager extends RelationManager
{
    protected static string $relationship = 'books';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        Group::make()
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Group::make()
                                            ->schema([
                                                TextInput::make('title'),
                                                Select::make('publisher_id')
                                                    ->relationship('publisher', 'name')
                                                    ->searchable()
                                                    ->native(false)
                                                    ->preload()
                                                    ->live()
                                                    ->afterStateUpdated(fn (Set $set) => $set('author_id', null)),
                                                Select::make('author_id')
                                                    ->options(fn (Get $get) => Author::where('publisher_id', $get('publisher_id'))
                                                        ->pluck('name', 'id'))
                                                    ->searchable()
                                                    ->native(false)
                                                    ->preload()
                                                    ->live(),
                                                Select::make('genre_id')
                                                    ->relationship('genre', 'name')
                                                    ->searchable()
                                                    ->native(false)
                                                    ->preload(),
                                            ])->columns(2),
                                        Group::make()
                                            ->schema([
                                                TextInput::make('isbn')
                                                    ->prefixIcon('heroicon-o-qr-code')
                                                    ->prefixIconColor('white')
                                                    ->numeric(),
                                                TextInput::make('price')
                                                    ->prefix('$')
                                                    ->mask(RawJs::make('$money($input)'))
                                                    ->stripCharacters(',')
                                                    ->numeric(),
                                                TextInput::make('stock')
                                                    ->prefixIcon('heroicon-o-archive-box')
                                                    ->prefixIconColor('white')
                                                    ->numeric(),
                                            ])->columns(3),
                                        RichEditor::make('description')
                                            ->disableToolbarButtons(['attachFiles'])
                                            ->columnSpanFull(),
                                    ]),
                            ])->columnSpan(['sm' => 2, 'md' => 2, 'xxl' => 5]),
                        Group::make()
                            ->schema([
                                Section::make()
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('cover_image')
                                            ->image()
                                            ->imageEditor()
                                            ->imageEditorAspectRatios([
                                                '1:1.6',
                                            ])
                                            ->optimize('webp')
                                            ->collection('coverBooks')
                                            ->responsiveImages()
                                            ->storeFileNamesIn('cover_image_file_names')
                                            ->deleteUploadedFileUsing(function ($record) {
                                                Storage::disk('public')->delete($record);
                                            }),
                                    ]),
                                Section::make()
                                    ->schema([
                                        DatePicker::make('published'),
                                        Toggle::make('available'),
                                    ]),
                            ])->columnSpan(['sm' => 2, 'md' => 1, 'xxl' => 1]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover_image')
                    ->collection('coverBooks')
                    ->conversion('thumb'),
                TextColumn::make('title'),
                TextColumn::make('author.name'),
                TextColumn::make('stock'),
                ToggleColumn::make('available'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()
                        ->before(function ($record) {
                            Storage::disk('public')->delete($record);
                        }),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function ($records) {
                            $records->each(function ($record) {
                                Storage::disk('public')->delete($record);
                            });
                        }),
                ]),
            ]);
    }
}
