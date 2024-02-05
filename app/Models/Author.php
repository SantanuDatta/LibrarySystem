<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Author extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'publisher_id',
        'name',
        'date_of_birth',
        'bio',
        'avatar',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    public static function booted(): void
    {
        static::creating(function ($model) {
            $cacheKey = 'NavigationCount'.class_basename($model).$model->getTable();
            Cache::flush($cacheKey);
        });

        static::deleting(function ($model) {
            $cacheKey = 'NavigationCount'.class_basename($model).$model->getTable();
            Cache::flush($cacheKey);
        });
    }
}
