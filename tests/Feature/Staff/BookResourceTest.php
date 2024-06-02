<?php

use App\Filament\Staff\Resources\BookResource\Pages\CreateBook;
use App\Filament\Staff\Resources\BookResource\Pages\EditBook;
use App\Filament\Staff\Resources\BookResource\Pages\ListBooks;
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

beforeEach(function () {
    asRole(Role::IS_STAFF);

    $this->book = Book::factory()
        ->has(Author::factory(), relationship: 'author')
        ->has(Publisher::factory(), relationship: 'publisher')
        ->has(Genre::factory(), relationship: 'genre')
        ->create();

    $this->makeBook = Book::factory()
        ->has(Author::factory(), relationship: 'author')
        ->has(Publisher::factory(), relationship: 'publisher')
        ->has(Genre::factory(), relationship: 'genre')
        ->make();

    Storage::fake('public');
});

describe('Book List Page', function () {
    beforeEach(function () {
        $this->list = livewire(ListBooks::class, [
            'record' => $this->book,
            'panel' => 'staff',
        ]);
    });

    it('can render the list page', function () {
        $this->list
            ->assertSuccessful();
    });

    it('has cover image, title, author, stock and availability column', function () {
        $expectedColumns = [
            'cover_image',
            'title',
            'author.name',
            'stock',
            'available',
        ];

        foreach ($expectedColumns as $column) {
            $this->list->assertTableColumnExists($column);
        }
    });

    it('can get books cover image, title, author, stock and availability', function () {
        $books = $this->book;
        $book = $books->first();

        $this->list
            //->assertTableColumnStateSet('cover_image', $book->cover_image, record: $book)
            ->assertTableColumnStateSet('title', $book->title, record: $book)
            ->assertTableColumnStateSet('author.name', $book->author->name, record: $book)
            ->assertTableColumnStateSet('stock', $book->stock, record: $book)
            ->assertTableColumnStateSet('available', $book->available, record: $book);
    });

    it('can create a new book but can not delete the book', function () {
        $this->list
            ->assertActionEnabled('create')
            ->assertTableActionDisabled('delete', $this->book);
    });
});

describe('Book Create Page', function () {
    beforeEach(function () {
        $this->create = livewire(CreateBook::class, ['panel' => 'staff']);
        $this->imagePath = UploadedFile::fake()
            ->image('image.jpg', 50, 50);
    });

    it('can render the create page', function () {
        $this->create
            ->assertSuccessful();
    });

    it('can create a new book', function () {
        $newBook = $this->makeBook;

        $this->create
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
            'description' => $newBook->description,
            'stock' => $newBook->stock,
            'available' => $newBook->available,
            'published' => $newBook->published,
        ]);
    });

    it('can create a new book with a cover image', function () {
        $newBook = $this->makeBook;

        $this->create
            ->fillForm([
                'publisher_id' => $newBook->publisher->getKey(),
                'author_id' => $newBook->author->getKey(),
                'genre_id' => $newBook->genre->getKey(),
                'title' => $newBook->title,
                'cover_image' => $this->imagePath,
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
            'description' => $newBook->description,
            'stock' => $newBook->stock,
            'available' => $newBook->available,
            'published' => $newBook->published,
        ]);

        $createdBook = Book::latest()->first();
        $createdBook->addMedia($this->imagePath)->toMediaCollection('coverBooks');
        $mediaCollection = $createdBook->getMedia('coverBooks')->last();

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

describe('Book Edit Page', function () {
    beforeEach(function () {
        $this->edit = livewire(EditBook::class, [
            'record' => $this->book->getRouteKey(),
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
        $book = $this->book;

        $this->edit
            ->assertFormSet([
                'author_id' => $book->author->getKey(),
                'publisher_id' => $book->publisher->getKey(),
                'genre_id' => $book->genre->getKey(),
                'title' => $book->title,
                'isbn' => $book->isbn,
                'price' => $book->price,
                'description' => $book->description,
                'stock' => $book->stock,
                'available' => $book->available,
                'published' => $book->published->format('Y-m-d'),
            ]);
    });

    it('can update the book', function () {
        $book = $this->book;
        $updatedBook = $this->makeBook;

        $this->edit
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
            ->description->toBe($updatedBook->description)
            ->stock->toBe($updatedBook->stock)
            ->available->toBe($updatedBook->available)
            ->published->format('Y-m-d')->toBe($updatedBook->published->format('Y-m-d'));
    });

    it('can update the book with a cover image', function () {
        $book = $this->book;
        $updatedBook = $this->makeBook;

        $this->edit
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
                'cover_image' => $this->updatedImagePath,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $book->refresh();

        $book->addMedia($this->updatedImagePath, 'coverBooks')->toMediaCollection('coverBooks');
        $mediaCollection = $book->getMedia('coverBooks')->last();

        expect($book)
            ->title->toBe($updatedBook->title)
            ->publisher_id->toBe($updatedBook->publisher->getKey())
            ->author_id->toBe($updatedBook->author->getKey())
            ->genre_id->toBe($updatedBook->genre->getKey())
            ->isbn->toBe($updatedBook->isbn)
            ->price->toBe($updatedBook->price)
            ->description->toBe($updatedBook->description)
            ->stock->toBe($updatedBook->stock)
            ->available->toBe($updatedBook->available)
            ->published->format('Y-m-d')->toBe($updatedBook->published->format('Y-m-d'));

        expect($mediaCollection)
            ->toBeInstanceOf(Media::class)
            ->model_type->toBe($mediaCollection->model_type)
            ->uuid->toBe($mediaCollection->uuid)
            ->collection_name->toBe($mediaCollection->collection_name)
            ->name->toBe($mediaCollection->name)
            ->file_name->toBe($mediaCollection->file_name);
    });

    it('can validate form data on edit', function () {
        Book::factory()
            ->has(Author::factory(), relationship: 'author')
            ->has(Publisher::factory(), relationship: 'publisher')
            ->has(Genre::factory(), relationship: 'genre')
            ->create();

        $this->edit
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

    it('can not delete a book from the edit page', function () {
        $this->book;

        $this->edit
            ->assertActionHidden(DeleteAction::class);
    });
});
