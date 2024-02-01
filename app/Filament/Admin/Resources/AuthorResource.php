<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AuthorResource\Pages;
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
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AuthorResource extends Resource
{
    protected static ?string $model = Author::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'Books & Transactions';

    protected static ?string $recordTitleAttribute = 'name';

    protected static int $globalSearchResultLimit = 20;

    public static function getNavigationItems(): array
    {
        [$navigationItem] = parent::getNavigationItems();
        $count = static::getModel()::count();
    
        return [
            $navigationItem
                ->badge($count, color: $count > 10 ? 'info' : 'gray'),
        ];
    }

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
                                        TextInput::make('name'),
                                        Select::make('publisher_id')
                                            ->relationship('publisher', 'name')
                                            ->native(false)
                                            ->searchable()
                                            ->preload(),
                                        DatePicker::make('date_of_birth'),
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
                                            ->deleteUploadedFileUsing(function ($record) {
                                                Storage::disk('public')->delete($record);
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
                            Storage::disk('public')->delete($record->avatar);
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListAuthors::route('/'),
            'create' => Pages\CreateAuthor::route('/create'),
            'edit' => Pages\EditAuthor::route('/{record}/edit'),
        ];
    }
}
