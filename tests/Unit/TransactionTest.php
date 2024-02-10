<?php

use App\Models\Book;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;

use function Pest\Laravel\seed;

beforeEach(function () {
    seed();
});

test('book is being borrowed by a user whose role is a borrower', function () {
    $user = User::factory()->create(['role_id' => Role::IS_BORROWER]);
    $transaction = Transaction::factory()
        ->for(Book::factory())
        ->for($user)
        ->create();
    expect($transaction->book)
        ->toBeInstanceOf(Book::class);
    $this->assertDatabaseHas('books', [
        'id' => $transaction->book->id,
        'title' => $transaction->book->title,
    ]);
});
