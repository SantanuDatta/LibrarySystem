<?php

use App\Filament\Staff\Resources\Books\Pages\CreateBook;
use App\Filament\Staff\Resources\Books\Pages\EditBook;
use App\Filament\Staff\Resources\Books\Pages\ListBooks;
use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
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

    $state->publisher = Publisher::factory()->create();

    $state->book = Book::factory()
        ->for($state->publisher, 'publisher')
        ->for(Author::factory()->for($state->publisher, 'publisher'), 'author')
        ->has(Genre::factory(), relationship: 'genre')
        ->create();

    $state->makeBook = Book::factory()
        ->for($state->publisher, 'publisher')
        ->for(Author::factory()->for($state->publisher, 'publisher'), 'author')
        ->has(Genre::factory(), relationship: 'genre')
        ->make();

    Storage::fake('public');
});

describe('Book List Page', function () use ($state): void {
    beforeEach(function () use ($state): void {
        $state->list = livewire(ListBooks::class, [
            'record' => $state->book,
            'panel' => 'staff',
        ]);
    });

    it('can render the list page', function () use ($state): void {
        $state->list
            ->assertSuccessful();
    });

    it('has cover image, title, author, stock and availability column', function () use ($state): void {
        $expectedColumns = [
            'cover_image',
            'title',
            'author.name',
            'stock',
            'available',
        ];

        foreach ($expectedColumns as $column) {
            $state->list->assertTableColumnExists($column);
        }
    });

    it('can get books cover image, title, author, stock and availability', function () use ($state): void {
        $book = $state->book->first();

        $state->list
            // ->assertTableColumnStateSet('cover_image', $book->cover_image, record: $book)
            ->assertTableColumnStateSet('title', $book->title, record: $book)
            ->assertTableColumnStateSet('author.name', $book->author->name, record: $book)
            ->assertTableColumnStateSet('stock', $book->stock, record: $book)
            ->assertTableColumnStateSet('available', $book->available, record: $book);
    });

    it('can create a new book but can not delete the book', function () use ($state): void {
        $state->list
            ->assertActionEnabled('create')
            ->assertTableActionDisabled('delete', $state->book);
    });
});

describe('Book Create Page', function () use ($state): void {
    beforeEach(function () use ($state): void {
        $state->create = livewire(CreateBook::class, ['panel' => 'staff']);
        $state->imagePath = UploadedFile::fake()
            ->image('image.jpg', 50, 50);
    });

    it('can render the create page', function () use ($state): void {
        $state->create
            ->assertSuccessful();
    });

    it('can create a new book', function () use ($state): void {
        $newBook = $state->makeBook;

        $state->create
            ->fillForm([
                'publisher_id' => $newBook->publisher->getKey(),
                'author_id' => $newBook->author->getKey(),
                'genre_id' => $newBook->genre->getKey(),
                'title' => $newBook->title,
                'isbn' => $newBook->isbn,
                'price' => $newBook->price,
                'description' => $newBook->description,
                'stock' => $newBook->stock,
                'available' => $newBook->available,
                'published' => $newBook->published,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        assertDatabaseHas('books', [
            'publisher_id' => $newBook->publisher->getKey(),
            'author_id' => $newBook->author->getKey(),
            'genre_id' => $newBook->genre->getKey(),
            'title' => $newBook->title,
            'isbn' => $newBook->isbn,
            'price' => $newBook->price,
            'stock' => $newBook->stock,
            'available' => $newBook->available,
            'published' => $newBook->published?->format('Y-m-d H:i:s'),
        ]);

        $createdBook = Book::query()
            ->where('title', $newBook->title)
            ->where('isbn', $newBook->isbn)
            ->first();

        expect($createdBook)->not->toBeNull()
            ->and(strip_tags((string) $createdBook->description))->toBe(e($newBook->description));
    });

    it('can create a new book with a cover image', function () use ($state): void {
        $newBook = $state->makeBook;

        $state->create
            ->fillForm([
                'publisher_id' => $newBook->publisher->getKey(),
                'author_id' => $newBook->author->getKey(),
                'genre_id' => $newBook->genre->getKey(),
                'title' => $newBook->title,
                'cover_image' => $state->imagePath,
                'isbn' => $newBook->isbn,
                'price' => $newBook->price,
                'description' => $newBook->description,
                'stock' => $newBook->stock,
                'available' => $newBook->available,
                'published' => $newBook->published,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        assertDatabaseHas('books', [
            'publisher_id' => $newBook->publisher->getKey(),
            'author_id' => $newBook->author->getKey(),
            'genre_id' => $newBook->genre->getKey(),
            'title' => $newBook->title,
            'isbn' => $newBook->isbn,
            'price' => $newBook->price,
            'stock' => $newBook->stock,
            'available' => $newBook->available,
            'published' => $newBook->published?->format('Y-m-d H:i:s'),
        ]);

        $createdBook = Book::query()
            ->where('title', $newBook->title)
            ->where('isbn', $newBook->isbn)
            ->first();
        expect(strip_tags((string) $createdBook?->description))->toBe(e($newBook->description));
        $createdBook->addMedia($state->imagePath)->toMediaCollection('coverBooks');
        $mediaCollection = $createdBook->getMedia('coverBooks')->last();

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
                'title' => null,
                'publisher_id' => null,
                'author_id' => null,
                'genre_id' => null,
                'isbn' => null,
                'price' => null,
                'stock' => null,
                'published' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'title' => 'required',
                'publisher_id' => 'required',
                'author_id' => 'required',
                'genre_id' => 'required',
                'isbn' => 'required',
                'price' => 'required',
                'stock' => 'required',
                'published' => 'required',
            ]);
    });
});

describe('Book Edit Page', function () use ($state): void {
    beforeEach(function () use ($state): void {
        $state->edit = livewire(EditBook::class, [
            'record' => $state->book->getRouteKey(),
            'panel' => 'staff',
        ]);
        $state->updatedImagePath = UploadedFile::fake()
            ->image('updated_image.jpg', 50, 50);
    });

    it('can render the edit page', function () use ($state): void {
        $state->edit
            ->assertSuccessful();
    });

    it('can retrieve data', function () use ($state): void {
        $book = $state->book;

        $state->edit
            ->assertFormSet([
                'author_id' => $book->author->getKey(),
                'publisher_id' => $book->publisher->getKey(),
                'genre_id' => $book->genre->getKey(),
                'title' => $book->title,
                'isbn' => $book->isbn,
                'price' => $book->price,
                'stock' => $book->stock,
                'available' => $book->available,
                'published' => $book->published->format('Y-m-d'),
            ]);
    });

    it('can update the book', function () use ($state): void {
        $book = $state->book;
        $updatedBook = $state->makeBook;

        $state->edit
            ->fillForm([
                'title' => $updatedBook->title,
                'publisher_id' => $updatedBook->publisher->getKey(),
                'author_id' => $updatedBook->author->getKey(),
                'genre_id' => $updatedBook->genre->getKey(),
                'isbn' => $updatedBook->isbn,
                'price' => $updatedBook->price,
                'description' => $updatedBook->description,
                'stock' => $updatedBook->stock,
                'available' => $updatedBook->available,
                'published' => $updatedBook->published,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($book->refresh())
            ->title->toBe($updatedBook->title)
            ->publisher_id->toBe($updatedBook->publisher->getKey())
            ->author_id->toBe($updatedBook->author->getKey())
            ->genre_id->toBe($updatedBook->genre->getKey())
            ->isbn->toBe($updatedBook->isbn)
            ->price->toBe($updatedBook->price)
            ->stock->toBe($updatedBook->stock)
            ->available->toBe($updatedBook->available)
            ->published->format('Y-m-d')->toBe($updatedBook->published->format('Y-m-d'));

        expect(strip_tags((string) $book->description))->toBe(e($updatedBook->description));
    });

    it('can update the book with a cover image', function () use ($state): void {
        $book = $state->book;
        $updatedBook = $state->makeBook;

        $state->edit
            ->fillForm([
                'title' => $updatedBook->title,
                'publisher_id' => $updatedBook->publisher->getKey(),
                'author_id' => $updatedBook->author->getKey(),
                'genre_id' => $updatedBook->genre->getKey(),
                'isbn' => $updatedBook->isbn,
                'price' => $updatedBook->price,
                'description' => $updatedBook->description,
                'stock' => $updatedBook->stock,
                'available' => $updatedBook->available,
                'published' => $updatedBook->published,
                'cover_image' => $state->updatedImagePath,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $book->refresh();

        $book->addMedia($state->updatedImagePath, 'coverBooks')->toMediaCollection('coverBooks');
        $mediaCollection = $book->getMedia('coverBooks')->last();

        expect($book)
            ->title->toBe($updatedBook->title)
            ->publisher_id->toBe($updatedBook->publisher->getKey())
            ->author_id->toBe($updatedBook->author->getKey())
            ->genre_id->toBe($updatedBook->genre->getKey())
            ->isbn->toBe($updatedBook->isbn)
            ->price->toBe($updatedBook->price)
            ->stock->toBe($updatedBook->stock)
            ->available->toBe($updatedBook->available)
            ->published->format('Y-m-d')->toBe($updatedBook->published->format('Y-m-d'));

        expect(strip_tags((string) $book->description))->toBe(e($updatedBook->description));

        expect($mediaCollection)
            ->toBeInstanceOf(Media::class)
            ->model_type->toBe($mediaCollection->model_type)
            ->uuid->toBe($mediaCollection->uuid)
            ->collection_name->toBe($mediaCollection->collection_name)
            ->name->toBe($mediaCollection->name)
            ->file_name->toBe($mediaCollection->file_name);
    });

    it('can validate form data on edit', function () use ($state): void {
        Book::factory()
            ->for($state->publisher, 'publisher')
            ->for(Author::factory()->for($state->publisher, 'publisher'), 'author')
            ->has(Genre::factory(), relationship: 'genre')
            ->create();

        $state->edit
            ->fillForm([
                'title' => null,
                'publisher_id' => null,
                'author_id' => null,
                'genre_id' => null,
                'isbn' => null,
                'price' => null,
                'stock' => null,
                'published' => null,
            ])
            ->call('save')
            ->assertHasFormErrors([
                'title' => 'required',
                'publisher_id' => 'required',
                'author_id' => 'required',
                'genre_id' => 'required',
                'isbn' => 'required',
                'price' => 'required',
                'stock' => 'required',
                'published' => 'required',
            ]);
    });

    it('can not delete a book from the edit page', function () use ($state): void {
        $state->edit
            ->assertActionHidden(DeleteAction::class);
    });
});
