<?php

use App\Enums\BorrowedStatus;
use App\Filament\Staff\Resources\Transactions\Pages\CreateTransaction;
use App\Filament\Staff\Resources\Transactions\Pages\EditTransaction;
use App\Filament\Staff\Resources\Transactions\Pages\ListTransactions;
use App\Models\Book;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

$state = new stdClass;

beforeEach(function () use ($state): void {
    asRole(Role::IS_STAFF);

    $state->user = User::factory([
        'role_id' => Role::getId(Role::IS_BORROWER),
        'status' => true,
    ])->create();

    $state->transaction = Transaction::factory()
        ->for(Book::factory([
            'available' => true,
        ]))
        ->for($state->user)
        ->create();

    $state->makeTransaction = Transaction::factory()
        ->for(Book::factory([
            'available' => true,
        ]))
        ->for($state->user)
        ->make();
});

describe('Relation check with the user', function () use ($state): void {
    test('book is being borrowed by a user whose role is a borrower', function () use ($state): void {
        $transaction = $state->transaction;

        expect($transaction->book)
            ->toBeInstanceOf(Book::class);

        assertDatabaseHas('books', [
            'id' => $transaction->book->getKey(),
            'title' => $transaction->book->title,
        ]);
    });
});

describe('Transaction List Page', function () use ($state): void {
    beforeEach(function () use ($state): void {
        $state->list = livewire(ListTransactions::class, [
            'record' => $state->transaction,
            'panel' => 'staff',
        ]);
    });

    it('can render the list page', function () use ($state): void {
        $state->list
            ->assertSuccessful();
    });

    it('has borrower name, borrowed book with date and the return date with status', function () use ($state): void {
        $expectedColumns = [
            'user.name',
            'book.title',
            'borrowed_date',
            'returned_date',
            'status',
        ];

        foreach ($expectedColumns as $column) {
            $state->list->assertTableColumnExists($column);
        }
    });

    it('can get borrower name, borrowed book with date and the return date with status', function () use ($state): void {
        $transaction = $state->transaction->first();

        $state->list
            ->assertTableColumnStateSet('user.name', $transaction->user->name, record: $transaction)
            ->assertTableColumnStateSet('book.title', $transaction->book->title, record: $transaction)
            ->assertTableColumnStateSet('borrowed_date', $transaction->borrowed_date, record: $transaction)
            ->assertTableColumnStateSet('returned_date', $transaction->returned_date, record: $transaction)
            ->assertTableColumnFormattedStateSet('status', $transaction->status->getLabel(), record: $transaction);
    });

    it('can create a new transaction but can not delete it', function () use ($state): void {
        $state->list
            ->assertActionEnabled('create')
            ->assertTableActionDisabled('delete', $state->transaction);
    });
});

describe('Transaction Create Page', function () use ($state): void {
    beforeEach(function () use ($state): void {
        $state->create = livewire(CreateTransaction::class, [
            'panel' => 'staff',
        ]);
    });

    it('can render the create page', function () use ($state): void {
        $state->create
            ->assertSuccessful();
    });

    it('can create a new transaction', function () use ($state): void {
        $newTransaction = $state->makeTransaction;

        $state->create
            ->fillForm([
                'book_id' => $newTransaction->book->getKey(),
                'user_id' => $newTransaction->user->getKey(),
                'borrowed_date' => $newTransaction->borrowed_date,
                'borrowed_for' => $newTransaction->borrowed_for,
                'status' => BorrowedStatus::Borrowed->value,
            ])
            ->call('create')
            ->assertFormFieldIsHidden('returned_date')
            ->assertHasNoFormErrors();

        assertDatabaseHas('transactions', [
            'book_id' => $newTransaction->book->getKey(),
            'user_id' => $newTransaction->user->getKey(),
            'borrowed_date' => $newTransaction->borrowed_date,
            'borrowed_for' => $newTransaction->borrowed_for,
            'status' => BorrowedStatus::Borrowed->value,
            'returned_date' => null,
        ]);
    });

    it('can validate form data on create', function () use ($state): void {
        $state->create
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

describe('Transaction Edit Page', function () use ($state): void {
    beforeEach(function () use ($state): void {
        $state->edit = livewire(EditTransaction::class, [
            'record' => $state->transaction->getRouteKey(),
            'panel' => 'staff',
        ]);
    });

    it('can render the edit page', function () use ($state): void {
        $state->edit
            ->assertSuccessful();
    });

    it('can retrieve data', function () use ($state): void {
        $transaction = $state->transaction;

        $state->edit
            ->assertFormSet([
                'book_id' => $transaction->book->getKey(),
                'user_id' => $transaction->user->getKey(),
                'borrowed_date' => $transaction->borrowed_date->format('Y-m-d'),
                'borrowed_for' => $transaction->borrowed_for,
                'status' => $transaction->status->value,
            ]);
    });

    it('can update the transaction when it is returned', function () use ($state): void {
        $transaction = $state->transaction;

        $updatedTransactionData = [
            'book_id' => $transaction->book->getKey(),
            'user_id' => $transaction->user->getKey(),
            'borrowed_date' => now(),
            'borrowed_for' => 10,
            'status' => BorrowedStatus::Returned,
            'returned_date' => now()->addDays(5),
        ];

        $transaction->update($updatedTransactionData);

        $state->edit
            ->fillForm($updatedTransactionData)
            ->call('save')
            ->assertHasNoFormErrors();

        $updatedTransaction = $transaction->refresh();

        expect($updatedTransaction)
            ->user_id->toBe($updatedTransactionData['user_id'])
            ->book_id->toBe($updatedTransactionData['book_id'])
            ->borrowed_date->format('Y-m-d')->toBe($updatedTransactionData['borrowed_date']->format('Y-m-d'))
            ->borrowed_for->toBe($updatedTransactionData['borrowed_for'])
            ->status->toBe($updatedTransactionData['status'])
            ->returned_date->format('Y-m-d')->toBe($updatedTransactionData['returned_date']->format('Y-m-d'));
    });

    it('can update the transaction when it is delayed and fine is applied', function () use ($state): void {
        $transaction = $state->transaction;

        $borrowedDate = now();
        $returnedDate = now()->addDays(15);
        $borrowedFor = 10;

        $updatedTransactionData = [
            'book_id' => $transaction->book->getKey(),
            'user_id' => $transaction->user->getKey(),
            'borrowed_date' => $borrowedDate,
            'borrowed_for' => $borrowedFor,
            'status' => BorrowedStatus::Delayed,
            'returned_date' => $returnedDate,
        ];

        $delayDate = abs($returnedDate->diffInDays($borrowedDate));
        $delayedFor = $delayDate - $borrowedFor;
        $fine = intval($delayedFor) * 10;

        $updatedTransactionData['fine'] = $fine;

        $transaction->update($updatedTransactionData);

        $state->edit
            ->fillForm($updatedTransactionData)
            ->call('save')
            ->assertHasNoFormErrors();

        $updatedTransaction = $transaction->refresh();

        expect($updatedTransaction)
            ->user_id->toBe($updatedTransactionData['user_id'])
            ->book_id->toBe($updatedTransactionData['book_id'])
            ->borrowed_date->format('Y-m-d')->toBe($updatedTransactionData['borrowed_date']->format('Y-m-d'))
            ->borrowed_for->toBe($updatedTransactionData['borrowed_for'])
            ->status->toBe($updatedTransactionData['status'])
            ->returned_date->format('Y-m-d')->toBe($updatedTransactionData['returned_date']->format('Y-m-d'))
            ->fine->toBe($updatedTransactionData['fine']);
    });

    it('can validate form data on edit', function () use ($state): void {
        $state->edit
            ->fillForm([
                'book_id' => null,
                'user_id' => null,
                'borrowed_date' => null,
                'borrowed_for' => null,
                'status' => BorrowedStatus::Returned->value,
                'returned_date' => null,
            ])
            ->assertFormFieldIsVisible('returned_date')
            ->call('save')
            ->assertHasFormErrors([
                'book_id' => 'required',
                'user_id' => 'required',
                'borrowed_date' => 'required',
                'borrowed_for' => 'required',
                'returned_date' => 'required',
            ]);
    });

    it('can not delate a transaction from the edit page', function () use ($state): void {
        $state->edit
            ->assertActionHidden(DeleteAction::class);
    });
});
