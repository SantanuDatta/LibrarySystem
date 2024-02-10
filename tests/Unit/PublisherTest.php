<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\Publisher;

it('has many authors', function () {
    $publisher = Publisher::factory()
        ->has(Author::factory()->count(3))
        ->create();
    expect($publisher->authors)
        ->toHaveCount(3);
    $this->assertDatabaseHas('authors', [
        'id' => $publisher->authors->first()->id,
        'name' => $publisher->authors->first()->name,
    ]);
});

it('has many authors with books', function () {
    $publisher = Publisher::factory()
        ->has(Author::factory()
            ->has(Book::factory())
            ->count(3))
        ->create();
    expect($publisher->authors->each(fn ($author) => $author->books))
        ->toHaveCount(3);
    $this->assertDatabaseHas('books', [
        'id' => $publisher->authors->first()->books->first()->id,
        'title' => $publisher->authors->first()->books->first()->title,
    ]);
});
