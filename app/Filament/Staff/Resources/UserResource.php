<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\UserResource\Pages;
use App\Http\Traits\BorrowerCount;
use App\Models\Role;
use App\Models\User;
use Filament\AvatarProviders\UiAvatarsProvider;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserResource extends Resource
{
    use BorrowerCount;

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Settings';

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
                                        TextInput::make('name')
                                            ->required(),
                                        TextInput::make('email')
                                            ->email()
                                            ->requiredWith('name')
                                            ->unique(ignoreRecord: true),
                                        TextInput::make('password')
                                            ->password()
                                            ->same('passwordConfirmation')
                                            ->revealable()
                                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                                            ->dehydrated(fn (?string $state): bool => filled($state))
                                            ->required(fn (string $operation): bool => $operation === 'create'),
                                        TextInput::make('passwordConfirmation')
                                            ->revealable()
                                            ->password()
                                            ->dehydrated(false)
                                            ->required(fn (string $operation): bool => $operation === 'create'),
                                        TextInput::make('address'),
                                        TextInput::make('phone')
                                            ->tel(),
                                    ])->columns(2),
                            ])->columnSpan(['sm' => 2, 'md' => 2, 'xxl' => 5]),
                        Group::make()
                            ->schema([
                                Section::make('User Avatar')
                                    ->schema([
                                        FileUpload::make('avatar_url')
                                            ->label('')
                                            ->image()
                                            ->imageEditor()
                                            ->avatar()
                                            ->deleteUploadedFileUsing(function ($record) {
                                                Storage::disk('public')->delete($record->avatar_url);
                                            })
                                            ->extraAttributes([
                                                'class' => 'justify-center',
                                            ]),
                                        Toggle::make('status'),
                                        Hidden::make('role_id')
                                            ->default(Role::whereName('borrower')->pluck('id')->first()),
                                    ]),
                            ])->columnSpan(['sm' => 2, 'md' => 1, 'xxl' => 1]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->defaultImageUrl(fn ($record) => $record->avatar_url
                        ?: (new UiAvatarsProvider())->get($record))
                    ->circular(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email'),
                TextColumn::make('role.name')
                    ->badge(),
                ToggleColumn::make('status'),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()
                        ->before(function ($record) {
                            Storage::disk('public')->delete($record->avatar_url);
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            Storage::disk('public')->delete($records->each->avatar_url);
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('role')->whereRelation('role', 'name', 'borrower');
    }
}
