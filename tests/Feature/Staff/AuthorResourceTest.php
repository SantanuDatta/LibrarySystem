<?php

use App\Filament\Staff\Resources\Authors\Pages\CreateAuthor;
use App\Filament\Staff\Resources\Authors\Pages\EditAuthor;
use App\Filament\Staff\Resources\Authors\Pages\ListAuthors;
use App\Filament\Staff\Resources\Authors\RelationManagers\BooksRelationManager;
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

$state = new stdClass;

beforeEach(function () use ($state): void {
    asRole(Role::IS_STAFF);

    $state->author = Author::factory()
        ->has(Publisher::factory())
        ->create();

    $state->makeAuthor = Author::factory()
        ->has(Publisher::factory())
        ->make();

    Storage::fake('public');
});

describe('Author List Page', function () use ($state): void {
    beforeEach(function () use ($state): void {
        $state->list = livewire(ListAuthors::class, [
            'record' => $state->author,
            'panel' => 'staff',
        ]);
    });

    it('can render the list page', function () use ($state): void {
        $state->list
            ->assertSuccessful();
    });

    it('can render author avatar, name, publisher and date of birth columns', function () use ($state): void {
        $expectedColumns = [
            'avatar',
            'name',
            'publisher.name',
            'date_of_birth',
        ];

        foreach ($expectedColumns as $column) {
            $state->list
                ->assertTableColumnExists($column)
                ->assertSuccessful();
        }
    });

    it('can get authors avatar, name, publisher and date of birth', function () use ($state): void {
        $author = $state->author->first();

        $state->list
            // ->assertTableColumnStateSet('avatar', $author->avatar, record: $author)
            ->assertTableColumnStateSet('name', $author->name, record: $author)
            ->assertTableColumnStateSet('publisher.name', $author->publisher->name, record: $author)
            ->assertTableColumnStateSet('date_of_birth', $author->date_of_birth, record: $author);
    });

    it('can create a new author but can not delete an author', function () use ($state): void {
        $state->list
            ->assertActionEnabled('create')
            ->assertTableActionDisabled('delete', $state->author);
    });
});

describe('Author Create Page', function () use ($state): void {
    beforeEach(function () use ($state): void {
        $state->create = livewire(CreateAuthor::class, ['panel' => 'staff']);
        $state->imagePath = UploadedFile::fake()
            ->image('image.jpg', 50, 50);
    });

    it('can render the create page', function () use ($state): void {
        $state->create
            ->assertSuccessful();
    });

    it('can create an author', function () use ($state): void {
        $newAuthor = $state->makeAuthor;

        $state->create
            ->fillForm([
                'name' => $newAuthor->name,
                'publisher_id' => $newAuthor->publisher->getKey(),
                'date_of_birth' => $newAuthor->date_of_birth,
                'bio' => $newAuthor->bio,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        assertDatabaseHas('authors', [
            'name' => $newAuthor->name,
            'publisher_id' => $newAuthor->publisher->getKey(),
            'date_of_birth' => $newAuthor->date_of_birth?->format('Y-m-d H:i:s'),
        ]);

        $createdAuthor = Author::query()
            ->where('name', $newAuthor->name)
            ->where('publisher_id', $newAuthor->publisher->getKey())
            ->first();

        expect($createdAuthor)->not->toBeNull()
            ->and(strip_tags((string) $createdAuthor->bio))->toBe(e($newAuthor->bio));
    });

    it('can create a new author with an avatar', function () use ($state): void {
        $newAuthor = $state->makeAuthor;

        $state->create
            ->fillForm([
                'name' => $newAuthor->name,
                'publisher_id' => $newAuthor->publisher->getKey(),
                'date_of_birth' => $newAuthor->date_of_birth,
                'bio' => $newAuthor->bio,
                'avatar' => $state->imagePath,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        assertDatabaseHas('authors', [
            'name' => $newAuthor->name,
            'publisher_id' => $newAuthor->publisher->getKey(),
            'date_of_birth' => $newAuthor->date_of_birth?->format('Y-m-d H:i:s'),
        ]);

        $createdAuthor = Author::query()
            ->where('name', $newAuthor->name)
            ->where('publisher_id', $newAuthor->publisher->getKey())
            ->first();
        expect(strip_tags((string) $createdAuthor?->bio))->toBe(e($newAuthor->bio));
        $createdAuthor->addMedia($state->imagePath, 'public')->toMediaCollection('avatars');
        $mediaCollection = $createdAuthor->getMedia('avatars')->last();

        expect($mediaCollection)
            ->toBeInstanceOf(Media::class)
            ->model_type->toBe($mediaCollection->model_type)
            ->uuid->toBe($mediaCollection->uuid)
            ->collection_name->toBe($mediaCollection->collection_name)
            ->name->toBe($mediaCollection->name)
            ->file_name->toBe($mediaCollection->file_name);
    });

    it('can validate form data on create', function () use ($state): void {
        $state->create
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

describe('Author Edit Page', function () use ($state): void {
    beforeEach(function () use ($state): void {
        $state->edit = livewire(EditAuthor::class, [
            'record' => $state->author->getRouteKey(),
            'panel' => 'staff',
        ]);
        $state->updatedImagePath = UploadedFile::fake()
            ->image('updated_image.jpg', 50, 50);
    });

    it('can render the edit page', function () use ($state): void {
        $state->edit
            ->assertSuccessful();
    });

    it('can update an author', function () use ($state): void {
        $author = $state->author;
        $updatedAuthor = $state->makeAuthor;

        $state->edit
            ->fillForm([
                'name' => $updatedAuthor->name,
                'publisher_id' => $updatedAuthor->publisher->getKey(),
                'date_of_birth' => $updatedAuthor->date_of_birth,
                'bio' => $updatedAuthor->bio,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($author->refresh())
            ->name->toBe($updatedAuthor->name)
            ->publisher_id->toBe($updatedAuthor->publisher->getKey())
            ->date_of_birth->format('Y-m-d')->toBe($author->date_of_birth->format('Y-m-d'))
            ->and(strip_tags((string) $author->bio))->toBe(e($updatedAuthor->bio));
    });

    it('can update an author with an avatar', function () use ($state): void {
        $author = $state->author;
        $updatedAuthor = $state->makeAuthor;

        $state->edit
            ->fillForm([
                'name' => $updatedAuthor->name,
                'publisher_id' => $updatedAuthor->publisher->getKey(),
                'date_of_birth' => $updatedAuthor->date_of_birth,
                'bio' => $updatedAuthor->bio,
                'avatar' => $state->updatedImagePath,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $author->refresh();

        $author->addMedia($state->updatedImagePath, 'public')->toMediaCollection('avatars');
        $mediaCollection = $author->getMedia('avatars')->last();

        expect($author)
            ->name->toBe($updatedAuthor->name)
            ->publisher_id->toBe($updatedAuthor->publisher->getKey())
            ->date_of_birth->format('Y-m-d')->toBe($author->date_of_birth->format('Y-m-d'))
            ->and(strip_tags((string) $author->bio))->toBe(e($updatedAuthor->bio));

        expect($mediaCollection)
            ->toBeInstanceOf(Media::class)
            ->model_type->toBe($mediaCollection->model_type)
            ->uuid->toBe($mediaCollection->uuid)
            ->collection_name->toBe($mediaCollection->collection_name)
            ->name->toBe($mediaCollection->name)
            ->file_name->toBe($mediaCollection->file_name);
    });

    it('can validate form data on edit', function () use ($state): void {
        Author::factory()
            ->has(Publisher::factory(), relationship: 'publisher')
            ->create();

        $state->edit
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

    it('can render a relation manager with books', function (): void {
        $author = Author::factory()
            ->has(Publisher::factory(), relationship: 'publisher')
            ->has(Book::factory()->count(10))
            ->create();

        livewire(BooksRelationManager::class, [
            'ownerRecord' => $author,
            'pageClass' => EditAuthor::class,
        ])->assertSuccessful();
    });

    it('can not delete an author from the edit page', function () use ($state): void {
        $state->edit
            ->assertActionHidden(DeleteAction::class);
    });
});
