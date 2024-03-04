<?php

namespace App\Observers;

use App\Enums\BorrowedStatus;
use App\Models\Transaction;
use App\Models\User;
use Filament\Notifications\Notification;

class TransactionObserver
{
    private $admin;

    public function __construct()
    {
        $this->admin = User::with('role')
            ->whereRelation('role', 'name', 'admin')
            ->first();
    }

    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        Notification::make()
            ->title($transaction->user->name.' Borrowed a book')
            ->icon('heroicon-o-user')
            ->info()
            ->sendToDatabase($this->admin);
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        if (auth()->user()->role->name == 'staff' && $transaction->status == BorrowedStatus::Returned) {
            Notification::make()
                ->title('A Borrower Returned a book')
                ->body($transaction->user->name.' returned a book on time')
                ->icon('heroicon-o-user')
                ->success()
                ->sendToDatabase($this->admin);
        }

        if (auth()->user()->role->name == 'staff' && $transaction->status == BorrowedStatus::Delayed) {
            Notification::make()
                ->title('A Borrower Delayed to return a book')
                ->body(
                    $transaction->user->name.' delayed to return a book, and had to pay a fine of $'.$transaction->fine
                )
                ->icon('heroicon-o-user')
                ->danger()
                ->sendToDatabase($this->admin);
        }
    }
}
