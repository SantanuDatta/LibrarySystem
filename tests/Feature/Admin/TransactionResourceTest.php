<?php

use App\Enums\BorrowedStatus;
use App\Filament\Admin\Resources\TransactionResource\Pages\CreateTransaction;
use App\Filament\Admin\Resources\TransactionResource\Pages\EditTransaction;
use App\Filament\Admin\Resources\TransactionResource\Pages\ListTransactions;
use App\Models\Book;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use Filament\Actions\DeleteAction as FormDeleteAction;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    asRole(Role::IS_ADMIN);

    $this->user = User::factory([
        'role_id' => Role::IS_BORROWER,
        'status' => true,
    ])
        ->create();

    $this->transaction = Transaction::factory()
        ->for(Book::factory([
            'available' => true,
        ]))
        ->for($this->user)
        ->create();

    $this->makeTransaction = Transaction::factory()
        ->for(Book::factory([
            'available' => true,
        ]))
        ->for($this->user)
        ->make();
});

describe('Relation check with the user', function () {
    test('book is being borrowed by a user whose role is a borrower', function () {
        $transaction = $this->transaction;

        expect($transaction->book)
            ->toBeInstanceOf(Book::class);

        $this->assertDatabaseHas('books', [
            'id' => $transaction->book->getKey(),
            'title' => $transaction->book->title,
        ]);
    });
});

describe('Transaction List Page', function () {
    beforeEach(function () {
        $this->list = livewire(ListTransactions::class, [
            'record' => $this->transaction,
            'panel' => 'admin',
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

    it('can delete a transaction', function () {
        $this->list
            ->callTableAction(TableDeleteAction::class, $this->transaction);
        assertModelMissing($this->transaction);
    });
});

describe('Transaction Create Page', function () {
    beforeEach(function () {
        $this->create = livewire(CreateTransaction::class, [
            'record' => $this->transaction,
            'panel' => 'admin',
        ]);
    });

    it('can render the create page', function () {
        $this->create
            ->assertSuccessful();
    });

    it('can create a new transaction', function () {
        $newTransaction = $this->makeTransaction;

        $this->create
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
            'panel' => 'admin',
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

    it('can update the transaction when it is returned', function () {
        $transaction = $this->transaction;
        $updatedTransaction = $this->makeTransaction;

        $updatedTransactionData = [
            'book_id' => $transaction->book->getKey(),
            'user_id' => $transaction->user->getKey(),
            'borrowed_date' => now(),
            'borrowed_for' => 10,
            'status' => BorrowedStatus::Returned,
            'returned_date' => now()->addDays(5),
        ];

        $transaction->update($updatedTransactionData);

        $this->edit
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

    it('can update the transaction when it is delayed and fine is applied', function () {
        $transaction = $this->transaction;
        $updatedTransaction = $this->makeTransaction;

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

        $fine =  intval($delayedFor) * 10;

        $updatedTransactionData['fine'] = $fine;

        $transaction->update($updatedTransactionData);

        $this->edit
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

    it('can validate form data on edit', function () {
        $this->transaction;
        $this->edit
            ->assertFormFieldIsVisible('returned_date')
            ->fillForm([
                'book_id' => null,
                'user_id' => null,
                'borrowed_date' => null,
                'borrowed_for' => null,
                'returned_date' => null,
            ])
            ->call('save')
            ->assertHasFormErrors([
                'book_id' => 'required',
                'user_id' => 'required',
                'borrowed_date' => 'required',
                'borrowed_for' => 'required',
                'returned_date' => 'required',
            ]);
    });

    it('can delete a transaction from the edit page', function () {
        $this->transaction;

        $this->edit
            ->callAction(FormDeleteAction::class);

        assertModelMissing($this->transaction);
    });
});
