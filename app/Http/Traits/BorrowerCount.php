<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Cache;

trait BorrowerCount
{
    public static function getNavigationItems(): array
    {
        $borrowerKey = 'BorrowerCount_'.class_basename(static::class);
        $cachedCount = Cache::remember($borrowerKey, now()->addMinutes(5), function () {
            return static::getModel()::with('role')->whereRelation('role', 'name', 'borrower')
                ->count();
        });
        [$navigationItem] = parent::getNavigationItems();

        return [
            $navigationItem
                ->badge($cachedCount, color: $cachedCount > 10 ? 'info' : 'primary'),
        ];
    }
}
