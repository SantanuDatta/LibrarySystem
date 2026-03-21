<?php

namespace App\Filament\Staff\Resources\Genres\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GenreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make([
                    Group::make([
                        TextInput::make('name')
                            ->required(),
                        ColorPicker::make('bg_color'),
                        ColorPicker::make('text_color'),

                    ])->columns(3),
                ])->columnSpanFull(),
            ]);
    }
}
