<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AuthorResource\Pages\CreateAuthor;
use App\Filament\Admin\Resources\AuthorResource\Pages\EditAuthor;
use App\Filament\Admin\Resources\AuthorResource\Pages\ListAuthors;
use App\Filament\Admin\Resources\AuthorResource\RelationManagers\BooksRelationManager;
use App\Http\Traits\NavigationCount;
use App\Models\Author;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
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

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static string|\UnitEnum|null $navigationGroup = 'Books & Transactions';

    protected static ?string $recordTitleAttribute = 'name';

    protected static int $globalSearchResultLimit = 20;

    /**
     * @param  Author  $record
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

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                                            ->deleteUploadedFileUsing(function ($file): void {
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

    public static function getRelations(): array
    {
        return [
            BooksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuthors::route('/'),
            'create' => CreateAuthor::route('/create'),
            'edit' => EditAuthor::route('/{record}/edit'),
        ];
    }
}
