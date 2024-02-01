<?php

namespace App\Listeners;

class UpdateUserStatus
{
    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $event->user->update(['status' => true]);
    }
}
