<?php

use App\Enums\BorrowedStatus;
use App\Filament\Staff\Resources\TransactionResource\Pages\CreateTransaction;
use App\Filament\Staff\Resources\TransactionResource\Pages\ListTransactions;
use App\Models\Book;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    asStaff();
    $this->user = User::factory([
        'role_id' => Role::IS_BORROWER,
        'status' => true,
    ])
        ->create();
    $this->transaction = Transaction::factory()
        ->for(Book::factory([
            'available' => true,
        ]))
        ->state([
            'user_id' => $this->user->getKey(),
        ])
        ->create();
});

test('book is being borrowed by a user whose role is a borrower', function () {
    $transaction = $this->transaction;

    expect($transaction->book)
        ->toBeInstanceOf(Book::class);

    $this->assertDatabaseHas('books', [
        'id' => $transaction->book->getKey(),
        'title' => $transaction->book->title,
    ]);
});

describe('Transaction List Page', function () {
    beforeEach(function () {
        $this->list = livewire(ListTransactions::class, [
            'record' => $this->transaction,
            'panel' => 'staff',
        ]);
    });

    it('can render the list page', function () {
        $this->list
            ->assertSuccessful();
    });

    it('has borrower name, borrowed book with date and the return date with status', function () {
        $expectedColumns = [
            'user.name',
            'book.title',
            'borrowed_date',
            'returned_date',
            'status',
        ];

        foreach ($expectedColumns as $column) {
            $this->list->assertTableColumnExists($column);
        }
    });

    it('can get borrower name, borrowed book with date and the return date with status', function () {
        $transactions = $this->transaction;
        $transaction = $transactions->first();

        $this->list
            ->assertTableColumnStateSet('user.name', $transaction->user->name, record: $transaction)
            ->assertTableColumnStateSet('book.title', $transaction->book->title, record: $transaction)
            ->assertTableColumnStateSet('borrowed_date', $transaction->borrowed_date, record: $transaction)
            ->assertTableColumnStateSet('returned_date', $transaction->returned_date, record: $transaction)
            ->assertTableColumnFormattedStateSet('status', $transaction->status->getLabel(), record: $transaction);
    });

    it('can create a new transaction but can not delete it', function () {
        $this->list
            ->assertActionEnabled('create')
            ->assertTableActionDisabled('delete', $this->transaction);
    });
});

describe('Transaction Create Page', function () {
    beforeEach(function () {
        $this->create = livewire(CreateTransaction::class, [
            'panel' => 'staff',
            'record' => $this->transaction,
        ]);
    });

    it('can render the create page', function () {
        $this->create
            ->assertSuccessful();
    });

    it('can create a new transaction', function () {
        $newTransaction = Transaction::factory()
            ->for(Book::factory([
                'available' => true,
            ]))
            ->for($this->user)
            ->state([
                'status' => BorrowedStatus::Borrowed,
            ])
            ->make();

        $this->create
            ->fillForm([
                'book_id' => $newTransaction->book->getKey(),
                'user_id' => $newTransaction->user->getKey(),
                'borrowed_date' => $newTransaction->borrowed_date,
                'borrowed_for' => $newTransaction->borrowed_for,
                'status' => $newTransaction->status->value,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        assertDatabaseHas('transactions', [
            'book_id' => $newTransaction->book->getKey(),
            'user_id' => $newTransaction->user->getKey(),
            'borrowed_date' => $newTransaction->borrowed_date,
            'borrowed_for' => $newTransaction->borrowed_for,
            'status' => $newTransaction->status->value,
        ]);
    });

    //Work In Progress Doesn't Work
    it('can validate form data on create', function ($newTransaction = null) {
        $newTransaction = $newTransaction ? $newTransaction() : $this->transaction;

        $this->create
            ->call('create')
            ->assertHasFormErrors();

        assertDatabaseMissing('transactions', [
            'user_id' => $newTransaction->user_id,
            'book_id' => $newTransaction->book_id,
            'borrowed_date' => $newTransaction->borrowed_date,
            'borrowed_for' => $newTransaction->borrowed_for,
        ]);
    })->with([
        [fn () => Transaction::factory()->state(['user_id' => null])->make(), 'Missing Borrower'],
        [fn () => Transaction::factory()->state(['book_id' => null])->make(), 'Missing Book'],
        [fn () => Transaction::factory()->state(['borrowed_date' => null])->make(), 'Missing Borrowed Date'],
        [fn () => Transaction::factory()->state(['borrowed_for' => null])->make(), 'Missing Borrowed For'],
    ]);
});
