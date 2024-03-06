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
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;
use function PHPUnit\Framework\assertEquals;

beforeEach(function () {
    asRole(Role::IS_STAFF);

    $this->author = Author::factory()
        ->has(Publisher::factory())
        ->create();

    $this->makeAuthor = Author::factory()
        ->has(Publisher::factory())
        ->make();
    Storage::fake('public');
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
        $this->imagePath = UploadedFile::fake()
            ->image('image.jpg', 50, 50);
    });

    it('can render the create page', function () {
        $this->create
            ->assertSuccessful();
    });

    it('can create a new author', function () {
        $newAuthor = $this->makeAuthor;

        $this->create
            ->fillForm([
                'name' => $newAuthor->name,
                'publisher_id' => $newAuthor->publisher->getKey(),
                'date_of_birth' => $newAuthor->date_of_birth,
                'bio' => $newAuthor->bio,
                'avatar' => $this->imagePath,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $createdAuthor = Author::whereName($newAuthor->name)->first();
        $createdAuthor->addMedia($this->imagePath, 'public')->toMediaCollection('avatars');
        $mediaCollection = $createdAuthor->getMedia('avatars')->last();

        expect([
            'name' => $newAuthor->name,
            'publisher_id' => $newAuthor->publisher->getKey(),
            'date_of_birth' => $newAuthor->date_of_birth->format('Y-m-d'),
            'bio' => $newAuthor->bio,
        ])->toBe([
            'name' => $createdAuthor->name,
            'publisher_id' => $createdAuthor->publisher->getKey(),
            'date_of_birth' => $createdAuthor->date_of_birth->format('Y-m-d'),
            'bio' => $createdAuthor->bio,
        ]);

        expect($mediaCollection)
            ->toBeInstanceOf(Media::class)
            ->model_type->toBe($mediaCollection->model_type)
            ->uuid->toBe($mediaCollection->uuid)
            ->collection_name->toBe($mediaCollection->collection_name)
            ->name->toBe($mediaCollection->name)
            ->file_name->toBe($mediaCollection->file_name);
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
        $this->updatedImagePath = UploadedFile::fake()
            ->image('updated_image.jpg', 50, 50);
    });

    it('can render the edit page', function () {
        $this->edit
            ->assertSuccessful();
    });

    it('can update an author with an avatar', function () {
        $author = $this->author;
        $updatedAuthor = $this->makeAuthor;

        $this->edit
            ->fillForm([
                'name' => $updatedAuthor->name,
                'publisher_id' => $updatedAuthor->publisher->getKey(),
                'date_of_birth' => $updatedAuthor->date_of_birth,
                'bio' => $updatedAuthor->bio,
                'avatar' => $this->updatedImagePath,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $author->refresh();

        $author->addMedia($this->updatedImagePath, 'public')->toMediaCollection('avatars');
        $mediaCollection = $author->getMedia('avatars')->last();

        expect($mediaCollection)
            ->toBeInstanceOf(Media::class)
            ->model_type->toBe($mediaCollection->model_type)
            ->uuid->toBe($mediaCollection->uuid)
            ->collection_name->toBe($mediaCollection->collection_name)
            ->name->toBe($mediaCollection->name)
            ->file_name->toBe($mediaCollection->file_name);

        expect($author)
            ->name->toBe($updatedAuthor->name)
            ->publisher_id->toBe($updatedAuthor->publisher->getKey())
            ->date_of_birth->format('Y-m-d')->toBe($author->date_of_birth->format('Y-m-d'))
            ->bio->toBe($author->bio);
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
