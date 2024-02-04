<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Cache;

trait NavigationCount
{
    public static function getNavigationItems(): array
    {
        $cacheKey = 'NavigationCount'.class_basename(static::class);
        $cachedCount = Cache::rememberForever($cacheKey, function () {
            return static::getModel()::count();
        });
        [$navigationItem] = parent::getNavigationItems();
        return [
            $navigationItem
                ->badge($cachedCount, color: $cachedCount > 10 ? 'info' : 'primary'),
        ];
    }
}
