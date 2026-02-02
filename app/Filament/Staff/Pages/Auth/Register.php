<?php

namespace App\Filament\Staff\Pages\Auth;

use App\Models\Role;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;

class Register extends \Filament\Auth\Pages\Register
{
    public function form(Schema $schema): Schema
    {
        return $this->makeForm()
            ->components([
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
