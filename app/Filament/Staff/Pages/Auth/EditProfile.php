<?php

namespace App\Filament\Staff\Pages\Auth;

use App\Models\User;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseProfile;
use Illuminate\Support\Facades\Storage;

class EditProfile extends BaseProfile
{
    public function form(Form $form): Form
    {
        return $this->makeForm()
            ->schema([
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
