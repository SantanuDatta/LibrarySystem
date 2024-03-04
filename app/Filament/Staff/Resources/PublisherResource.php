<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\PublisherResource\Pages;
use App\Http\Traits\NavigationCount;
use App\Models\Publisher;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
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
use Illuminate\Support\Facades\Storage;

class PublisherResource extends Resource
{
    use NavigationCount;

    protected static ?string $model = Publisher::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $navigationGroup = 'Books & Transactions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
            'index' => Pages\ListPublishers::route('/'),
            'create' => Pages\CreatePublisher::route('/create'),
            'edit' => Pages\EditPublisher::route('/{record}/edit'),
        ];
    }
}
