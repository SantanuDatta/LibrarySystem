<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Cache;

trait NavigationCount
{
    public static function getNavigationItems(): array
    {
        $cacheKey = 'NavigationCount_'.class_basename(static::class);
        $cachedCount = Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return static::getModel()::count();
        });
        [$navigationItem] = parent::getNavigationItems();

        return [
            $navigationItem
                ->badge($cachedCount, color: $cachedCount > 10 ? 'info' : 'primary'),
        ];
    }
}
