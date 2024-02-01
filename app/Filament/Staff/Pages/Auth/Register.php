<?php

namespace App\Filament\Staff\Pages\Auth;

use App\Models\Role;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as BaseRegister;

class Register extends BaseRegister
{
    public function form(Form $form): Form
    {
        return $this->makeForm()
            ->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getRoleFormComponent(),
            ])
            ->statePath('data');
    }

    protected function getRoleFormComponent(): Component
    {
        return Hidden::make('role_id')
            ->default(Role::whereName('staff')->first()->id);
    }
}
