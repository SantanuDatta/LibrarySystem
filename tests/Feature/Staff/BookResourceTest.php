<?php

use App\Filament\Staff\Resources\BookResource\Pages\CreateBook;
use App\Filament\Staff\Resources\BookResource\Pages\EditBook;
use App\Filament\Staff\Resources\BookResource\Pages\ListBooks;
use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Publisher;
use Illuminate\Http\UploadedFile;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;
use function PHPUnit\Framework\assertTrue;

beforeEach(function () {
    asStaff();
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
            ->assertTableColumnStateSet('cover_image', $book->cover_image, record: $book)
            ->assertTableColumnStateSet('title', $book->title, record: $book)
            ->assertTableColumnStateSet('author.name', $book->author->name, record: $book)
            ->assertTableColumnStateSet('stock', $book->stock, record: $book)
            ->assertTableColumnStateSet('available', $book->available, record: $book);
    });

    it('can create a new book but cannot delete the book', function () {
        $this->list
            ->assertActionEnabled('create')
            ->assertTableActionDisabled('delete', $this->book);
    });
});

describe('Book Create Page', function () {
    beforeEach(function () {
        $this->create = livewire(CreateBook::class, ['panel' => 'staff']);
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
                'publisher_id' => $newBook->publisher_id,
                'author_id' => $newBook->author_id,
                'genre_id' => $newBook->genre_id,
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
            'publisher_id' => $newBook->publisher_id,
            'author_id' => $newBook->author_id,
            'genre_id' => $newBook->genre_id,
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

    it('can validate form data on create', function (Book $newBook) {
        $this->create
            ->call('create')
            ->assertHasFormErrors();

        assertDatabaseMissing('books', [
            'title' => $newBook->title,
            'publisher_id' => $newBook->publisher_id,
            'author_id' => $newBook->author_id,
            'genre_id' => $newBook->genre_id,
            'isbn' => $newBook->isbn,
            'price' => $newBook->price,
            'stock' => $newBook->stock,
            'published' => $newBook->published,
        ]);
    })->with([
        [fn () => Book::factory()->state(['title' => null])->make(), 'Missing Title'],
        [fn () => Book::factory()->state(['publisher_id' => null])->make(), 'Missing Publisher'],
        [fn () => Book::factory()->state(['author_id' => null])->make(), 'Missing Author'],
        [fn () => Book::factory()->state(['genre_id' => null])->make(), 'Missing Genre'],
        [fn () => Book::factory()->state(['isbn' => null])->make(), 'Missing ISBN'],
        [fn () => Book::factory()->state(['price' => null])->make(), 'Missing Price'],
        [fn () => Book::factory()->state(['stock' => null])->make(), 'Missing Stock'],
        [fn () => Book::factory()->state(['published' => null])->make(), 'Missing Published Date'],
    ]);
});

describe('Book Edit Page', function () {
    beforeEach(function () {
        $this->edit = livewire(EditBook::class, [
            'record' => $this->book->getRouteKey(),
            'panel' => 'staff',
        ]);
    });

    it('can render the edit page', function () {
        $this->edit
            ->assertSuccessful();
    });

    it('can update the book', function () {
        $book = $this->book;

        $updatedBookData = Book::factory()
            ->has(Author::factory(), relationship: 'author')
            ->has(Publisher::factory(), relationship: 'publisher')
            ->has(Genre::factory(), relationship: 'genre')
            ->state([
                'author_id' => Author::factory(),
                'publisher_id' => Publisher::factory(),
                'genre_id' => Genre::factory(),
                'title' => fake()->name(),
                'isbn' => fake()->unique()->isbn13(),
                'price' => fake()->randomFloat(2, 0, 100),
                'description' => fake()->realText(600),
                'stock' => fake()->numberBetween(0, 100),
                'available' => fake()->boolean(50),
                'published' => fake()->dateTimeThisCentury(),
            ])
            ->create();

        $updateBookCover = UploadedFile::fake()->image('update_book_cover.jpg');

        $this->edit
            ->fillForm([
                'title' => $updatedBookData->title,
                'publisher_id' => $updatedBookData->publisher_id,
                'author_id' => $updatedBookData->author_id,
                'genre_id' => $updatedBookData->genre_id,
                'price' => $updatedBookData->price,
                'description' => $updatedBookData->description,
                'stock' => $updatedBookData->stock,
                'available' => $updatedBookData->available,
                'published' => $updatedBookData->published,
                'cover_image' => $updateBookCover,
            ])
            ->set('isbn', $updatedBookData->isbn)
            ->call('save')
            ->assertHasNoFormErrors();

        $updatedBook = $book->refresh();

        expect($updatedBook)
            ->title->toBe($updatedBook->title)
            ->publisher_id->toBe($updatedBook->publisher_id)
            ->author_id->toBe($updatedBook->author_id)
            ->genre_id->toBe($updatedBook->genre_id)
            ->isbn->toBe($updatedBook->isbn)
            ->price->toBe($updatedBook->price)
            ->description->toBe($updatedBook->description)
            ->stock->toBe($updatedBook->stock)
            ->available->toBe($updatedBook->available)
            ->published->format('Y-m-d')->toBe($updatedBook->published->format('Y-m-d'));

        expect($updatedBook->getFirstMedia('coverBooks'))->not->toBeNull();

        assertDatabaseHas('books', [
            'title' => $updatedBook->title,
            'publisher_id' => $updatedBook->publisher_id,
            'author_id' => $updatedBook->author_id,
            'genre_id' => $updatedBook->genre_id,
            'price' => $updatedBook->price,
            'description' => $updatedBook->description,
            'stock' => $updatedBook->stock,
            'available' => $updatedBook->available,
            'published' => $updatedBook->published,
        ]);

        assertDatabaseHas('media', [
            'model_type' => Book::class,
            'model_id' => $updatedBook->id,
            'uuid' => $updatedBook->getFirstMedia('coverBooks')->uuid,
            'collection_name' => 'coverBooks',
        ]);
    });

    it('can validate form data on edit', function (Book $updateBook) {
        $this->edit
            ->fillForm([
                'title' => $updateBook->title,
                'publisher_id' => $updateBook->publisher_id,
                'author_id' => $updateBook->author_id,
                'genre_id' => $updateBook->genre_id,
                'price' => $updateBook->price,
                'isbn' => $updateBook->isbn,
                'stock' => $updateBook->stock,
                'published' => $updateBook->published,
            ])
            ->call('save')
            ->assertHasFormErrors();
    })->with([
        [fn () => Book::factory()->state(['title' => null])->make(), 'Missing Title'],
        [fn () => Book::factory()->state(['publisher_id' => null])->make(), 'Missing Publisher'],
        [fn () => Book::factory()->state(['author_id' => null])->make(), 'Missing Author'],
        [fn () => Book::factory()->state(['genre_id' => null])->make(), 'Missing Genre'],
        [fn () => Book::factory()->state(['isbn' => null])->make(), 'Missing ISBN'],
        [fn () => Book::factory()->state(['price' => null])->make(), 'Missing Price'],
        [fn () => Book::factory()->state(['stock' => null])->make(), 'Missing Stock'],
        [fn () => Book::factory()->state(['published' => null])->make(), 'Missing Published Date'],
    ]);
});
