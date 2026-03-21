<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\UserResource\Pages\CreateUser;
use App\Filament\Staff\Resources\UserResource\Pages\EditUser;
use App\Filament\Staff\Resources\UserResource\Pages\ListUsers;
use App\Http\Traits\BorrowerCount;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\AvatarProviders\UiAvatarsProvider;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
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

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                                            ->required()
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
                                            ->directory('users')
                                            ->deleteUploadedFileUsing(function ($file): void {
                                                Storage::disk('public')->delete($file);
                                            })
                                            ->extraAttributes([
                                                'class' => 'justify-center',
                                            ]),
                                        Toggle::make('status'),
                                        Hidden::make('role_id')
                                            ->default(Role::whereName('borrower')
                                                ->value('id')),
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
                    ->label('Avatar')
                    ->defaultImageUrl(fn ($record) => $record->avatar_url
                        ?: (new UiAvatarsProvider)->get($record))
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
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()
                        ->before(function ($record): void {
                            if (! is_null($record->avatar_url)) {
                                Storage::disk('public')->delete($record->avatar_url);
                            }
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function ($records): void {
                            $records->each(function ($record): void {
                                if (! is_null($record->avatar_url)) {
                                    Storage::disk('public')->delete($record->avatar_url);
                                }
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('role')->whereRelation('role', 'name', 'borrower');
    }
}
