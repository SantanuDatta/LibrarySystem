<?php

namespace App\Filament\Admin\Pages;

use App\Settings\GeneralSettings;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Support\Facades\Storage;

class ManageGeneral extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = GeneralSettings::class;

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'General Settings';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        Group::make()
                            ->schema([
                                Section::make('General Settings')
                                    ->schema([
                                        TextInput::make('site_name'),
                                        Select::make('site_active')
                                            ->options([
                                                true => 'Active',
                                                false => 'Inactive',
                                            ])
                                            ->native(false),
                                        TextInput::make('site_logoWidth'),
                                        TextInput::make('site_logoHeight'),
                                    ]),
                            ])->columnSpan(['sm' => 2, 'md' => 2, 'xxl' => 5]),
                        Group::make()
                            ->schema([
                                Section::make('Favicon & Logo')
                                    ->schema([
                                        FileUpload::make('site_favicon')
                                            ->image()
                                            ->directory('sites')
                                            ->acceptedFileTypes(['image/x-icon', 'image/vnd.microsoft.icon'])
                                            ->deleteUploadedFileUsing(fn ($file) => Storage::disk('public')->delete($file))
                                            ->nullable(),
                                        FileUpload::make('site_logo')
                                            ->image()
                                            ->directory('sites')
                                            ->label('Site Logo (General)')
                                            ->deleteUploadedFileUsing(fn ($file) => Storage::disk('public')->delete($file))
                                            ->nullable(),
                                    ])->columnSpanFull(),
                                Section::make('Dark Mode Logo [Optional]')
                                    ->schema([
                                        FileUpload::make('site_logo_dark')
                                            ->image()
                                            ->directory('sites')
                                            ->label('Site Logo (Dark Mode)')
                                            ->deleteUploadedFileUsing(fn ($file) => Storage::disk('public')->delete($file))
                                            ->nullable(),
                                    ])->collapsed(),
                            ])->columnSpan(['sm' => 2, 'md' => 1, 'xxl' => 1]),
                    ]),
            ]);
    }
}
