<?php

namespace App\Filament\Staff\Resources\Users;

use App\Filament\Staff\Resources\Users\Pages\CreateUser;
use App\Filament\Staff\Resources\Users\Pages\EditUser;
use App\Filament\Staff\Resources\Users\Pages\ListUsers;
use App\Filament\Staff\Resources\Users\Schemas\UserForm;
use App\Filament\Staff\Resources\Users\Tables\UsersTable;
use App\Http\Traits\BorrowerCount;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    use BorrowerCount;

    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
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
