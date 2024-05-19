<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\BookResource\Pages;
use App\Http\Traits\NavigationCount;
use App\Models\Author;
use App\Models\Book;
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
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class BookResource extends Resource
{
    use NavigationCount;

    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Books & Transactions';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $globalSearchResultLimit = 20;

    /**
     * @param Book $record
     * @return array
     */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Author' => $record->author->name,
            'Publisher' => $record->publisher->name,
            'Genre' => $record->genre->name,
        ];
    }

    public static function form(Form $form): Form
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
                                        Group::make()
                                            ->schema([
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
                                            ->responsiveImages(true)
                                            ->deleteUploadedFileUsing(function ($file) {
                                                Storage::disk('public')->delete($file);
                                            }),
                                    ]),
                                Section::make()
                                    ->schema([
                                        DatePicker::make('published')
                                            ->required(),
                                        Toggle::make('available'),
                                    ]),
                            ])->columnSpan(['sm' => 2, 'md' => 1, 'xxl' => 1]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
        ];
    }
}
