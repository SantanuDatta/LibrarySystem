<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Cache;

trait BorrowerCount
{
    public static function getNavigationItems(): array
    {
        $borrowerKey = 'BorrowerCount'.class_basename(static::class);
        $cachedCount = Cache::rememberForever($borrowerKey, function () {
            return static::getModel()::with('role')->whereRelation('role', 'name', 'borrower')->count();
        });
        [$navigationItem] = parent::getNavigationItems();

        return [
            $navigationItem
                ->badge($cachedCount, color: $cachedCount > 10 ? 'info' : 'primary'),
        ];
    }
}
