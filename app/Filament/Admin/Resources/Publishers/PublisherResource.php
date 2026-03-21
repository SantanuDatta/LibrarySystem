<?php

namespace App\Filament\Admin\Resources\Publishers;

use App\Filament\Admin\Resources\Publishers\Pages\CreatePublisher;
use App\Filament\Admin\Resources\Publishers\Pages\EditPublisher;
use App\Filament\Admin\Resources\Publishers\Pages\ListPublishers;
use App\Filament\Admin\Resources\Publishers\Schemas\PublisherForm;
use App\Filament\Admin\Resources\Publishers\Tables\PublishersTable;
use App\Http\Traits\NavigationCount;
use App\Models\Publisher;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PublisherResource extends Resource
{
    use NavigationCount;

    protected static ?string $model = Publisher::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-paper-airplane';

    protected static string|\UnitEnum|null $navigationGroup = 'Books & Transactions';

    public static function form(Schema $schema): Schema
    {
        return PublisherForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PublishersTable::configure($table);
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
