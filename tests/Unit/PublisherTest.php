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
});

it('has many authors with books', function () {
    $publisher = Publisher::factory()
        ->has(Author::factory()
            ->has(Book::factory())
            ->count(3))
        ->create();
    expect($publisher->authors->each(fn ($author) => $author->books))
        ->toHaveCount(3);
});

