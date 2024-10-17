<?php

use App\Filament\Staff\Resources\UserResource\Pages\CreateUser;
use App\Filament\Staff\Resources\UserResource\Pages\EditUser;
use App\Filament\Staff\Resources\UserResource\Pages\ListUsers;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;
use function PHPUnit\Framework\assertEquals;

beforeEach(function () {
    asRole(Role::IS_STAFF);

    $this->user = User::factory([
        'role_id' => Role::getId(Role::IS_BORROWER),
    ])
        ->create();

    $this->makeUser = User::factory([
        'role_id' => Role::getId(Role::IS_BORROWER),
    ])
        ->make();

    Storage::fake('public');
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
        $this->imagePath = UploadedFile::fake()
            ->image('image.jpg', 50, 50);
    });

    it('can render the create page', function () {
        $this->create
            ->assertSuccessful();
    });

    it('can create a user', function () {
        $newUser = $this->makeUser;

        $hashedPassword = Hash::make($newUser->password);

        $this->create
            ->fillForm([
                'name' => $newUser->name,
                'email' => $newUser->email,
                'password' => $hashedPassword,
                'passwordConfirmation' => $hashedPassword,
                'address' => $newUser->address,
                'phone' => $newUser->phone,
                'status' => $newUser->status,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $createdUser = User::whereName($newUser->name)->first();

        expect($createdUser)
            ->name->toBe($newUser->name)
            ->email->toBe($newUser->email)
            ->address->toBe($newUser->address)
            ->phone->toBe($newUser->phone)
            ->status->toBe($newUser->status);
        assertEquals($createdUser->role_id, $newUser->role_id);
        expect($createdUser);

        expect(Hash::check($newUser->password, $hashedPassword))->toBeTrue();
    });

    it('can create a user with an avatar', function () {
        $newUser = $this->makeUser;

        $hashedPassword = Hash::make($newUser->password);

        $this->create
            ->fillForm([
                'avatar_url.0' => $this->imagePath->hashName(),
                'name' => $newUser->name,
                'email' => $newUser->email,
                'password' => $hashedPassword,
                'passwordConfirmation' => $hashedPassword,
                'address' => $newUser->address,
                'phone' => $newUser->phone,
                'status' => $newUser->status,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        assertDatabaseHas('users', [
            'avatar_url' => $this->imagePath->hashName(),
            'name' => $newUser->name,
            'email' => $newUser->email,
            'address' => $newUser->address,
            'phone' => $newUser->phone,
            'role_id' => $newUser->role_id,
            'status' => $newUser->status,
        ]);

        expect(Hash::check($newUser->password, $hashedPassword))->toBeTrue();
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
        $this->updatedImagePath = UploadedFile::fake()
            ->image('updated_image.jpg', 50, 50);
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

    it('can update user', function () {
        $user = $this->user;
        $updatedUser = $this->makeUser;

        $this->edit
            ->fillForm([
                'name' => $updatedUser->name,
                'email' => $updatedUser->email,
                'password' => $updatedUser->password,
                'passwordConfirmation' => $updatedUser->password,
                'address' => $updatedUser->address,
                'phone' => $updatedUser->phone,
                'status' => $updatedUser->status,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($user->refresh())
            ->name->toBe($updatedUser->name)
            ->email->toBe($updatedUser->email)
            ->address->toBe($updatedUser->address)
            ->phone->toBe($updatedUser->phone)
            ->status->toBe($updatedUser->status);

        if ($updatedUser['password']) {
            expect(Hash::check($updatedUser['password'], $user->password))->toBeTrue();
        }
    });

    it('can update user with an avatar', function () {
        $user = $this->user;

        $updatedUser = $this->makeUser;

        $this->edit
            ->fillForm([
                'avatar_url.0' => $this->updatedImagePath->hashName(),
                'name' => $updatedUser->name,
                'email' => $updatedUser->email,
                'password' => $updatedUser->password,
                'passwordConfirmation' => $updatedUser->password,
                'address' => $updatedUser->address,
                'phone' => $updatedUser->phone,
                'status' => $updatedUser->status,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($user->refresh())
            ->avatar_url->toBe($this->updatedImagePath->hashName())
            ->name->toBe($updatedUser->name)
            ->email->toBe($updatedUser->email)
            ->address->toBe($updatedUser->address)
            ->phone->toBe($updatedUser->phone)
            ->status->toBe($updatedUser->status);

        if ($updatedUser['password']) {
            expect(Hash::check($updatedUser['password'], $user->password))->toBeTrue();
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
