<?php

namespace App\Http\Traits;

trait NavigationCount
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
        return static::getModel()::count();
    }
}
