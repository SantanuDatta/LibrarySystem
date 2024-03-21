<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\AuthorResource\Pages;
use App\Filament\Staff\Resources\AuthorResource\RelationManagers\BooksRelationManager;
use App\Http\Traits\NavigationCount;
use App\Models\Author;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AuthorResource extends Resource
{
    use NavigationCount;

    protected static ?string $model = Author::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'Books & Transactions';

    protected static ?string $recordTitleAttribute = 'name';

    protected static int $globalSearchResultLimit = 20;

    /**
     * @param Author $record
     * @return array
     */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Publisher' => $record->publisher->name,
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('publisher');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        Group::make()
                            ->schema([
                                Section::make("Author's Profile")
                                    ->schema([
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
                        Group::make()
                            ->schema([
                                Section::make('Avatar')
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('avatar')
                                            ->label('')
                                            ->image()
                                            ->avatar()
                                            ->imageEditor()
                                            ->circleCropper()
                                            ->responsiveImages(true)
                                            ->optimize('webp')
                                            ->collection('avatars')
                                            ->deleteUploadedFileUsing(function ($file) {
                                                Storage::disk('public')->delete($file);
                                            })
                                            ->extraAttributes([
                                                'class' => 'justify-center',
                                            ]),
                                    ]),
                            ])->columnSpan(['sm' => 2, 'md' => 1, 'xxl' => 1]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('avatar')
                    ->collection('avatars')
                    ->circular()
                    ->conversion('thumb')
                    ->extraImgAttributes(['loading' => 'lazy']),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('publisher.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('date_of_birth')
                    ->date('d M, Y'),
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
            BooksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuthors::route('/'),
            'create' => Pages\CreateAuthor::route('/create'),
            'edit' => Pages\EditAuthor::route('/{record}/edit'),
        ];
    }
}
