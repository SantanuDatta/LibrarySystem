<?php

use App\Filament\Admin\Resources\BookResource\Pages\CreateBook;
use App\Filament\Admin\Resources\BookResource\Pages\EditBook;
use App\Filament\Admin\Resources\BookResource\Pages\ListBooks;
use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Publisher;
use App\Models\Role;
use Filament\Actions\DeleteAction as FormDeleteAction;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Illuminate\Http\UploadedFile;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;
use function PHPUnit\Framework\assertTrue;

beforeEach(function () {
    asRole(Role::IS_ADMIN);

    $this->book = Book::factory()
        ->has(Author::factory(), relationship: 'author')
        ->has(Publisher::factory(), relationship: 'publisher')
        ->has(Genre::factory(), relationship: 'genre')
        ->create();
});

describe('Book List Page', function () {
    beforeEach(function () {
        $this->list = livewire(ListBooks::class, [
            'record' => $this->book,
            'panel' => 'admin',
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
            ->assertTableColumnStateSet('cover_image', $book->cover_image, record: $book)
            ->assertTableColumnStateSet('title', $book->title, record: $book)
            ->assertTableColumnStateSet('author.name', $book->author->name, record: $book)
            ->assertTableColumnStateSet('stock', $book->stock, record: $book)
            ->assertTableColumnStateSet('available', $book->available, record: $book);
    });

    it('can delete a book without a cover', function () {
        $this->list
            ->callTableAction(TableDeleteAction::class, $this->book);
        assertModelMissing($this->book);
    });

    it('can delete a book with cover', function () {
        $book = $this->book->getFirstMedia('coverBooks');

        $this->list
            ->callTableAction(TableDeleteAction::class, $this->book);

        assertModelMissing($this->book);

        if ($book !== null) {
            assertDatabaseMissing('media', [
                'model_type' => Book::class,
                'model_id' => $this->book->id,
                'collection_name' => 'coverBooks',
            ]);
        }
    });
});

describe('Book Create Page', function () {
    beforeEach(function () {
        $this->create = livewire(CreateBook::class, ['panel' => 'admin']);
    });

    it('can render the create page', function () {
        $this->create
            ->assertSuccessful();
    });

    it('can create a new book', function () {
        $newBook = Book::factory()
            ->has(Author::factory())
            ->has(Publisher::factory())
            ->make();

        $coverTitlePath = UploadedFile::fake()->image('cover_title.jpg');

        $this->create
            ->fillForm([
                'publisher_id' => $newBook->publisher->getKey(),
                'author_id' => $newBook->author->getKey(),
                'genre_id' => $newBook->genre->getKey(),
                'title' => $newBook->title,
                'cover_image' => $coverTitlePath,
                'isbn' => $newBook->isbn,
                'price' => $newBook->price,
                'description' => $newBook->description,
                'stock' => $newBook->stock,
                'available' => $newBook->available,
                'published' => $newBook->published,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $createdBook = Book::whereTitle($newBook->title)->first();

        assertTrue($createdBook->hasMedia('coverBooks'));

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

        assertDatabaseHas('media', [
            'model_type' => Book::class,
            'model_id' => $createdBook->id,
            'uuid' => $createdBook->getFirstMedia('coverBooks')->uuid,
            'collection_name' => 'coverBooks',
        ]);
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
            'panel' => 'admin',
        ]);
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

        $updatedBook = $book->make();

        $updateBookCover = UploadedFile::fake()->image('update_book_cover.jpg');

        $updatedBookData = [
            'title' => $book->title,
            'publisher_id' => $book->publisher->getKey(),
            'author_id' => $book->author->getKey(),
            'genre_id' => $book->genre->getKey(),
            'isbn' => $book->isbn,
            'price' => $book->price,
            'description' => $book->description,
            'stock' => $book->stock,
            'available' => $book->available,
            'published' => $book->published,
            'cover_image' => $updateBookCover,
        ];

        $book->update($updatedBookData);

        $this->edit
            ->fillForm($updatedBookData)
            ->call('save')
            ->assertHasNoFormErrors();

        $updatedBook = $book->refresh();

        expect($updatedBook)
            ->title->toBe($updatedBookData['title'])
            ->publisher_id->toBe($updatedBookData['publisher_id'])
            ->author_id->toBe($updatedBookData['author_id'])
            ->genre_id->toBe($updatedBookData['genre_id'])
            ->isbn->toBe($updatedBookData['isbn'])
            ->price->toBe($updatedBookData['price'])
            ->description->toBe($updatedBookData['description'])
            ->stock->toBe($updatedBookData['stock'])
            ->available->toBe($updatedBookData['available'])
            ->published->format('Y-m-d')->toBe($updatedBookData['published']->format('Y-m-d'));

        expect($updatedBook->getFirstMedia('coverBooks'))->not->toBeNull();

        assertDatabaseHas('media', [
            'model_type' => Book::class,
            'model_id' => $updatedBook->id,
            'uuid' => $updatedBook->getFirstMedia('coverBooks')->uuid,
            'collection_name' => 'coverBooks',
        ]);
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

    it('can delete a book from the edit page without a cover', function () {
        $this->book;

        $this->edit
            ->callAction(FormDeleteAction::class);

        assertModelMissing($this->book);
    });

    it('can delete a book from the edit page with a cover', function () {
        $book = $this->book->getFirstMedia('coverBooks');

        $this->edit
            ->callAction(FormDeleteAction::class);

        assertModelMissing($this->book);

        if ($book !== null) {
            assertDatabaseMissing('media', [
                'model_type' => Book::class,
                'model_id' => $this->book->id,
                'collection_name' => 'coverBooks',
            ]);
        }
    });
});
