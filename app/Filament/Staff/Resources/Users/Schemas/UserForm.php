<?php

namespace App\Filament\Staff\Resources\Users\Schemas;

use App\Models\Role;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Section::make([
                            Group::make([
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
                        Section::make('User Avatar')
                            ->schema([
                                Group::make([
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
                    ])->columnSpanFull(),
            ]);
    }
}
