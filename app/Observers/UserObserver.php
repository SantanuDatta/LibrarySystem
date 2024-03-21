<?php

namespace App\Observers;

use App\Models\User;
use Filament\Notifications\Notification;

class UserObserver
{
    private $admin;

    public function __construct()
    {
        $this->admin = User::with('role')
            ->whereRelation('role', 'name', 'admin')
            ->first();
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Check if the user is a borrower
        if (auth()->user() && auth()->user()->role->name == 'staff' && $user->role && $user->role->name == 'borrower') {
            Notification::make()
                ->title('New Borrower has been Registered')
                ->body(
                    $user->name . ' has been registered as a borrower. And is in the process to borrow a new book for few days'
                )
                ->icon('heroicon-o-user')
                ->success()
                ->sendToDatabase($this->admin);
        }
    }
}
