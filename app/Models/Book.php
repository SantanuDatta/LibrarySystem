<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Book extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'author_id',
        'publisher_id',
        'genre_id',
        'title',
        'cover_image',
        'isbn',
        'price',
        'description',
        'stock',
        'available',
        'published',
    ];

    protected $casts = [
        'available' => 'boolean',
        'published' => 'date',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }

    public static function booted(): void
    {
        static::creating(function ($model) {
            $cacheKey = 'NavigationCount'.class_basename($model);
            Cache::flush($cacheKey);
        });

        static::deleting(function ($model) {
            $cacheKey = 'NavigationCount'.class_basename($model);
            Cache::flush($cacheKey);
        });
    }
}
