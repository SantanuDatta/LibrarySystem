<?php

namespace App\Filament\Admin\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Admin\Resources\PublisherResource\Pages\ListPublishers;
use App\Filament\Admin\Resources\PublisherResource\Pages\CreatePublisher;
use App\Filament\Admin\Resources\PublisherResource\Pages\EditPublisher;
use App\Filament\Admin\Resources\PublisherResource\Pages;
use App\Http\Traits\NavigationCount;
use App\Models\Publisher;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class PublisherResource extends Resource
{
    use NavigationCount;

    protected static ?string $model = Publisher::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-paper-airplane';

    protected static string | \UnitEnum | null $navigationGroup = 'Books & Transactions';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Group::make()
                            ->schema([
                                Section::make("Publisher's Profile")
                                    ->schema([
                                        TextInput::make('name')
                                            ->required(),
                                        DatePicker::make('founded')
                                            ->required(),
                                    ])->columns(2),
                            ])->columnSpan(['sm' => 2, 'md' => 2, 'xxl' => 5]),
                        Group::make()
                            ->schema([
                                Section::make('Logo')
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('logo')
                                            ->label('')
                                            ->avatar()
                                            ->image()
                                            ->imageEditor()
                                            ->collection('publishers')
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
                SpatieMediaLibraryImageColumn::make('logo')
                    ->collection('publishers')
                    ->circular()
                    ->conversion('thumb'),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('founded')
                    ->date('d M, Y'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()
                        ->before(function ($record) {
                            Storage::disk('public')->delete($record);
                        }),
                ]),
            ])
            ->toolbarActions([
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
            'index' => ListPublishers::route('/'),
            'create' => CreatePublisher::route('/create'),
            'edit' => EditPublisher::route('/{record}/edit'),
        ];
    }
}
