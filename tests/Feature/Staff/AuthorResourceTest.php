<?php

use App\Filament\Staff\Resources\AuthorResource\Pages\CreateAuthor;
use App\Filament\Staff\Resources\AuthorResource\Pages\EditAuthor;
use App\Filament\Staff\Resources\AuthorResource\Pages\ListAuthors;
use App\Filament\Staff\Resources\AuthorResource\RelationManagers\BooksRelationManager;
use App\Models\Author;
use App\Models\Book;
use App\Models\Publisher;
use App\Models\Role;
use Filament\Actions\DeleteAction;
use Illuminate\Http\UploadedFile;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;
use function PHPUnit\Framework\assertTrue;

beforeEach(function () {
    asRole(Role::IS_STAFF);
    $this->author = Author::factory()
        ->has(Publisher::factory())
        ->create();
});

describe('Author List Page', function () {
    beforeEach(function () {
        $this->list = livewire(ListAuthors::class, [
            'record' => $this->author,
            'panel' => 'staff',
        ]);
    });

    it('can render the list page', function () {
        $this->list
            ->assertSuccessful();
    });

    it('can render author avatar, name, publisher and date of birth columns', function () {
        $expectedColumns = [
            'avatar',
            'name',
            'publisher.name',
            'date_of_birth',
        ];

        foreach ($expectedColumns as $column) {
            $this->list
                ->assertTableColumnExists($column)
                ->assertSuccessful();
        }
    });

    it('can get authors avatar, name, publisher and date of birth', function () {
        $authors = $this->author;
        $author = $authors->first();

        $this->list
            ->assertTableColumnStateSet('avatar', $author->avatar, record: $author)
            ->assertTableColumnStateSet('name', $author->name, record: $author)
            ->assertTableColumnStateSet('publisher.name', $author->publisher->name, record: $author)
            ->assertTableColumnStateSet('date_of_birth', $author->date_of_birth, record: $author);
    });

    it('can create a new author but can not delete an author', function () {
        $this->list
            ->assertActionEnabled('create')
            ->assertTableActionDisabled('delete', $this->author);
    });
});

describe('Author Create Page', function () {
    beforeEach(function () {
        $this->create = livewire(CreateAuthor::class, ['panel' => 'staff']);
    });

    it('can render the create page', function () {
        $this->create
            ->assertSuccessful();
    });

    it('can create a new author', function () {
        $newAuthor = Author::factory()
            ->has(Publisher::factory(), relationship: 'publisher')
            ->make();

        $avatarPath = UploadedFile::fake()->image('avatar.jpg');

        $this->create
            ->fillForm([
                'name' => $newAuthor->name,
                'publisher_id' => $newAuthor->publisher->getKey(),
                'date_of_birth' => $newAuthor->date_of_birth,
                'bio' => $newAuthor->bio,
                'avatar' => $avatarPath,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $createdAuthor = Author::whereName($newAuthor->name)->first();

        assertTrue($createdAuthor->hasMedia('avatars'));

        assertDatabaseHas('authors', [
            'name' => $newAuthor->name,
            'publisher_id' => $newAuthor->publisher->getKey(),
            'date_of_birth' => $newAuthor->date_of_birth,
            'bio' => $newAuthor->bio,
        ]);

        assertDatabaseHas('media', [
            'model_type' => Author::class,
            'model_id' => $createdAuthor->id,
            'uuid' => $createdAuthor->getFirstMedia('avatars')->uuid,
            'collection_name' => 'avatars',
        ]);
    });

    it('can validate form data on create', function () {
        $this->create
            ->fillForm([
                'name' => null,
                'publisher_id' => null,
                'date_of_birth' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'publisher_id' => 'required',
                'date_of_birth' => 'required',
            ]);
    });
});

describe('Author Edit Page', function () {
    beforeEach(function () {
        $this->edit = livewire(EditAuthor::class, [
            'record' => $this->author->getRouteKey(),
            'panel' => 'staff',
        ]);
    });

    it('can render the edit page', function () {
        $this->edit
            ->assertSuccessful();
    });

    it('can update an author', function () {
        $author = $this->author;

        $updateAuthorData = Author::factory()
            ->has(Publisher::factory(), relationship: 'publisher')
            ->make();

        $updatedAvatarPath = UploadedFile::fake()->image('new_avatar_image.jpg');

        $this->edit
            ->fillForm([
                'name' => $updateAuthorData->name,
                'publisher_id' => $updateAuthorData->publisher->getKey(),
                'date_of_birth' => $updateAuthorData->date_of_birth,
                'bio' => $updateAuthorData->bio,
                'avatar' => $updatedAvatarPath,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $updatedAuthor = $author->refresh();

        expect($updatedAuthor)
            ->name->toBe($updatedAuthor->name)
            ->publisher_id->toBe($updatedAuthor->publisher->getKey())
            ->date_of_birth->format('Y-m-d')->toBe($updatedAuthor->date_of_birth->format('Y-m-d'))
            ->bio->toBe($updatedAuthor->bio);

        expect($updatedAuthor->getFirstMedia('avatars'))->not->toBeNull();

        assertDatabaseHas('authors', [
            'name' => $updatedAuthor->name,
            'publisher_id' => $updatedAuthor->publisher->getKey(),
            'date_of_birth' => $updatedAuthor->date_of_birth,
            'bio' => $updatedAuthor->bio,
        ]);

        assertDatabaseHas('media', [
            'model_type' => Author::class,
            'model_id' => $updatedAuthor->id,
            'uuid' => $updatedAuthor->getFirstMedia('avatars')->uuid,
            'collection_name' => 'avatars',
        ]);

    });

    it('can validate form data on edit', function () {
        Author::factory()
            ->has(Publisher::factory(), relationship: 'publisher')
            ->create();
        $this->edit
            ->fillForm([
                'name' => null,
                'publisher_id' => null,
                'date_of_birth' => null,
            ])
            ->call('save')
            ->assertHasFormErrors([
                'name' => 'required',
                'publisher_id' => 'required',
                'date_of_birth' => 'required',
            ]);
    });

    it('can render a relation manager with books', function () {
        $author = Author::factory()
            ->has(Publisher::factory(), relationship: 'publisher')
            ->has(Book::factory()->count(10))
            ->create();

        livewire(BooksRelationManager::class, [
            'ownerRecord' => $author,
            'pageClass' => EditAuthor::class,
        ])->assertSuccessful();
    });

    it('can not delete an author from the edit page', function () {
        $this->author;

        $this->edit
            ->assertActionHidden(DeleteAction::class);
    });
});
