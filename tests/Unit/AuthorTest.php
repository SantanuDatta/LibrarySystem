<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\Publisher;

it('has a publisher', function () {
    $author = Author::factory()
        ->for(Publisher::factory())
        ->create();
    expect($author->publisher)
        ->toBeInstanceOf(Publisher::class);
    $this->assertDatabaseHas('publishers', [
        'id' => $author->publisher->id,
        'name' => $author->publisher->name,
    ]);
});

it('has many books', function () {
    $author = Author::factory()
        ->has(Book::factory()->count(3))
        ->create();
    expect($author->books)
        ->toHaveCount(3);
    $this->assertDatabaseHas('books', [
        'id' => $author->books->first()->id,
        'title' => $author->books->first()->title,
    ]);
});
