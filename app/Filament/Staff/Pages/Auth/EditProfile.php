<?php

namespace App\Filament\Staff\Pages\Auth;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Component;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Storage;

class EditProfile extends \Filament\Auth\Pages\EditProfile
{
    public function form(Schema $schema): Schema
    {
        return $this->makeForm()
            ->components([
                $this->getAvatarFormComponent(),
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getAddressFormComponent(),
                $this->getPhoneFormComponent(),
            ])
            ->operation('edit')
            ->model($this->getUser())
            ->statePath('data');
    }

    protected function getAvatarFormComponent(): Component
    {
        return FileUpload::make('avatar_url')
            ->label('Upload Avatar')
            ->image()
            ->imageEditor()
            ->avatar()
            ->directory('users')
            ->deleteUploadedFileUsing(fn (User $record) => Storage::disk('public')
                ->delete($record->avatar_url))
            ->extraAttributes([
                'class' => 'justify-center',
            ]);
    }

    protected function getAddressFormComponent(): Component
    {
        return TextInput::make('address');
    }

    protected function getPhoneFormComponent(): Component
    {
        return TextInput::make('phone')
            ->tel();
    }
}
