<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Publisher;

it('belongs to an author', function () {
    $book = Book::factory()
        ->for(Author::factory())
        ->create();
    expect($book->author)
        ->toBeInstanceOf(Author::class);
    $this->assertDatabaseHas('authors', [
        'id' => $book->author->id,
        'name' => $book->author->name,
    ]);
});

it('belongs to a publisher', function () {
    $book = Book::factory()
        ->for(Publisher::factory())
        ->create();
    expect($book->publisher)
        ->toBeInstanceOf(Publisher::class);
    $this->assertDatabaseHas('publishers', [
        'id' => $book->publisher->id,
        'name' => $book->publisher->name,
    ]);
});

it('belongs to a genre', function () {
    $book = Book::factory()
        ->for(Genre::factory())
        ->create();
    expect($book->genre)
        ->toBeInstanceOf(Genre::class);
    $this->assertDatabaseHas('genres', [
        'id' => $book->genre->id,
        'name' => $book->genre->name,
    ]);
});
