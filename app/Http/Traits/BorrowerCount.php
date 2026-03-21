<?php

namespace App\Http\Traits;

use App\Models\Role;

trait BorrowerCount
{
    public static function getNavigationBadge(): ?string
    {
        return (string) static::getNavigationBadgeCount();
    }

    public static function getNavigationBadgeColor(): string
    {
        return static::getNavigationBadgeCount() > 10 ? 'info' : 'primary';
    }

    protected static function getNavigationBadgeCount(): int
    {
        return static::getModel()::where('role_id', Role::getId(Role::IS_BORROWER))
            ->count();
    }
}
