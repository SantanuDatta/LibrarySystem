<?php

use App\Enums\BorrowedStatus;
use App\Filament\Staff\Resources\TransactionResource\Pages\CreateTransaction;
use App\Filament\Staff\Resources\TransactionResource\Pages\EditTransaction;
use App\Filament\Staff\Resources\TransactionResource\Pages\ListTransactions;
use App\Models\Book;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;
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
            'record' => $this->transaction,
            'panel' => 'staff',
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
                'borrowed_date' => now()->subDays(10),
                'borrowed_for' => 10,
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
            ->assertFormFieldIsHidden('returned_date')
            ->assertHasNoFormErrors();

        assertDatabaseHas('transactions', [
            'book_id' => $newTransaction->book->getKey(),
            'user_id' => $newTransaction->user->getKey(),
            'borrowed_date' => $newTransaction->borrowed_date,
            'borrowed_for' => $newTransaction->borrowed_for,
            'status' => $newTransaction->status->value,
        ]);
    });

    it('can validate form data on create', function () {
        $this->create
            ->fillForm([
                'book_id' => null,
                'user_id' => null,
                'borrowed_date' => null,
                'borrowed_for' => null,
            ])
            ->call('create')
            ->assertFormFieldIsHidden('returned_date')
            ->assertHasFormErrors([
                'book_id' => 'required',
                'user_id' => 'required',
                'borrowed_date' => 'required',
                'borrowed_for' => 'required',
            ]);
    });
});

describe('Transaction Edit Page', function () {
    beforeEach(function () {
        $this->edit = livewire(EditTransaction::class, [
            'record' => $this->transaction->getRouteKey(),
            'panel' => 'staff',
        ]);
    });

    it('can render the edit page', function () {
        $this->edit
            ->assertSuccessful();
    });

    it('can retrieve data', function () {
        $transaction = $this->transaction;

        $this->edit
            ->assertFormSet([
                'book_id' => $transaction->book->getKey(),
                'user_id' => $transaction->user->getKey(),
                'borrowed_date' => $transaction->borrowed_date->format('Y-m-d'),
                'borrowed_for' => $transaction->borrowed_for,
                'status' => $transaction->status->value,
            ]);
    });

    //Brainstorming need more time to think
    it('can update the transaction when it is returned', function () {
        $transaction = $this->transaction;

        $updatedTransactionData = Transaction::factory()
            ->for(Book::factory([
                'available' => true,
            ]))
            ->for($this->user)
            ->state([
                'borrowed_date' => now()->subDays(10),
                'borrowed_for' => 10,
                'status' => BorrowedStatus::Returned,
            ])
            ->make();

        $this->edit
            ->fillForm([
                'book_id' => $updatedTransactionData->book->getKey(),
                'user_id' => $updatedTransactionData->user->getKey(),
                'borrowed_date' => $updatedTransactionData->borrowed_date,
                'borrowed_for' => $updatedTransactionData->borrowed_for,
                'status' => $updatedTransactionData->status,
                'returned_date' => $updatedTransactionData->returned_date,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $updatedTransaction = $transaction->refresh();

        expect($updatedTransaction)
            ->user_id->toBe($updatedTransaction->user->getKey())
            ->book_id->toBe($updatedTransaction->book->getKey())
            ->borrowed_date->format('Y-m-d')->toBe($updatedTransaction->borrowed_date->format('Y-m-d'))
            ->borrowed_for->toBe($updatedTransaction->borrowed_for)
            ->status->toBe($updatedTransaction->status)
            ->returned_date->format('Y-m-d')->toBe($updatedTransaction->returned_date->format('Y-m-d'));

        assertDatabaseHas('transactions', [
            'book_id' => $updatedTransaction->book->getKey(),
            'user_id' => $updatedTransaction->user->getKey(),
            'borrowed_date' => $updatedTransaction->borrowed_date,
            'borrowed_for' => $updatedTransaction->borrowed_for,
            'status' => $updatedTransaction->status,
        ]);
    });

    //Brainstorming need more time to think
    it('can update the transaction when it is delayed', function () {
        $transaction = $this->transaction;

        $updatedTransactionData = Transaction::factory()
            ->for(Book::factory([
                'available' => true,
            ]))
            ->for($this->user)
            ->state([
                'borrowed_date' => now()->subDays(20),
                'borrowed_for' => 10,
                'status' => BorrowedStatus::Delayed,
            ])
            ->make();

        $this->edit
            ->fillForm([
                'book_id' => $updatedTransactionData->book->getKey(),
                'user_id' => $updatedTransactionData->user->getKey(),
                'borrowed_date' => $updatedTransactionData->borrowed_date,
                'borrowed_for' => $updatedTransactionData->borrowed_for,
                'status' => $updatedTransactionData->status,
                'returned_date' => $updatedTransactionData->returned_date,

            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $updatedTransaction = $transaction->refresh();

        expect($updatedTransaction)
            ->user_id->toBe($updatedTransaction->user->getKey())
            ->book_id->toBe($updatedTransaction->book->getKey())
            ->borrowed_date->format('Y-m-d')->toBe($updatedTransaction->borrowed_date->format('Y-m-d'))
            ->borrowed_for->toBe($updatedTransaction->borrowed_for)
            ->status->toBe($updatedTransaction->status)
            ->returned_date->format('Y-m-d')->toBe($updatedTransaction->returned_date->format('Y-m-d'));

        assertDatabaseHas('transactions', [
            'book_id' => $updatedTransaction->book->getKey(),
            'user_id' => $updatedTransaction->user->getKey(),
            'borrowed_date' => $updatedTransaction->borrowed_date,
            'borrowed_for' => $updatedTransaction->borrowed_for,
            'status' => $updatedTransaction->status,
        ]);
    });

});
