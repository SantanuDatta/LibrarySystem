<?php

use App\Filament\Staff\Resources\UserResource\Pages\CreateUser;
use App\Filament\Staff\Resources\UserResource\Pages\EditUser;
use App\Filament\Staff\Resources\UserResource\Pages\ListUsers;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;
use function PHPUnit\Framework\assertTrue;

beforeEach(function () {
    asRole(Role::IS_STAFF);

    $this->user = User::factory([
        'role_id' => Role::IS_BORROWER,
    ])
        ->create();
});

describe('User List Page', function () {
    beforeEach(function () {
        $this->list = livewire(ListUsers::class, [
            'record' => $this->user,
            'panel' => 'staff',
        ]);
    });

    it('can render the list page', function () {
        $this->list
            ->assertSuccessful();
    });

    it('has users avatar, name, email, role and status', function () {
        $expectedColumns = [
            'avatar_url',
            'name',
            'email',
            'role.name',
            'status',
        ];

        foreach ($expectedColumns as $column) {
            $this->list->assertTableColumnExists($column);
        }
    });

    it('can get users avatar, name, email, role and status', function () {
        $users = $this->user;
        $user = $users->first();

        $this->list
            ->assertTableColumnStateSet('avatar_url', $user->avatar_url, record: $user)
            ->assertTableColumnStateSet('name', $user->name, record: $user)
            ->assertTableColumnStateSet('email', $user->email, record: $user)
            ->assertTableColumnStateSet('role.name', $user->role->name, record: $user)
            ->assertTableColumnStateSet('status', $user->status, record: $user);
    });

    it('can create a user but cannot delete it', function () {
        $this->list
            ->assertActionEnabled('create')
            ->assertTableActionDisabled('delete', record: $this->user);
    });
});

describe('User Create Page', function () {
    beforeEach(function () {
        $this->create = livewire(CreateUser::class, [
            'panel' => 'staff',
        ]);
    });

    it('can render the create page', function () {
        $this->create
            ->assertSuccessful();
    });

    it('can create a user', function () {
        $newUser = User::factory([
            'role_id' => Role::IS_BORROWER,
        ])
            ->make();

        $hassedPassword = Hash::make($newUser->password);

        $this->create
            ->fillForm([
                'avatar_url' => $newUser->avatar_url,
                'name' => $newUser->name,
                'email' => $newUser->email,
                'password' => $hassedPassword,
                'passwordConfirmation' => $hassedPassword,
                'address' => $newUser->address,
                'phone' => $newUser->phone,
                'role_id' => $newUser->role_id,
                'status' => $newUser->status,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        assertDatabaseHas('users', [
            'avatar_url' => $newUser->avatar_url,
            'name' => $newUser->name,
            'email' => $newUser->email,
            'address' => $newUser->address,
            'phone' => $newUser->phone,
            'role_id' => $newUser->role_id,
            'status' => $newUser->status,
        ]);

        assertTrue(Hash::check($newUser->password, $hassedPassword));
    });

    it('can validate form data on create', function () {
        $this->create
            ->fillForm([
                'name' => null,
                'email' => null,
                'password' => null,
                'passwordConfirmation' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'email' => 'required',
                'password' => 'required',
                'passwordConfirmation' => 'required',
            ]);
    });
});

describe('User Edit Page', function () {
    beforeEach(function () {
        $this->edit = livewire(EditUser::class, [
            'record' => $this->user->getRouteKey(),
            'panel' => 'staff',
        ]);
    });

    it('can render the edit page', function () {
        $this->edit
            ->assertSuccessful();
    });

    it('can retrieve data', function () {
        $user = $this->user;

        $this->edit
            ->assertFormSet([
                'name' => $user->name,
                'email' => $user->email,
                'password' => null,
                'passwordConfirmation' => null,
                'address' => $user->address,
                'phone' => $user->phone,
                'role_id' => $user->role_id,
                'status' => $user->status,
            ]);
    });

    it('can update a user', function () {
        $user = $this->user;

        $updatedUser = $user->make();

        $updatedUserData = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $user->password,
            'passwordConfirmation' => $user->password,
            'address' => $user->address,
            'phone' => $user->phone,
            'status' => $user->status,
        ];

        $user->update($updatedUserData);

        $this->edit
            ->fillForm($updatedUserData)
            ->call('save')
            ->assertHasNoFormErrors();

        $updatedUser = $user->refresh();

        expect($updatedUser)
            ->name->toBe($updatedUserData['name'])
            ->email->toBe($updatedUserData['email'])
            ->address->toBe($updatedUserData['address'])
            ->phone->toBe($updatedUserData['phone'])
            ->status->toBe($updatedUserData['status']);

        if ($updatedUserData['password']) {
            assertTrue(Hash::check($updatedUserData['password'], $updatedUser->password));
        }
    });

    it('can validate form data on edit', function () {
        $this->user;

        $this->edit
            ->fillForm([
                'name' => null,
                'email' => null,
            ])
            ->call('save')
            ->assertHasFormErrors([
                'name' => 'required',
                'email' => 'required',
            ]);
    });

    it('can not delete the user from edit page', function () {
        $this->user;

        $this->edit
            ->assertActionHidden('delete');
    });
});
