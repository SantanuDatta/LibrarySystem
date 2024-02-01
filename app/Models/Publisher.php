<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Publisher extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'founded',
        'logo',
    ];

    protected $casts = [
        'founded' => 'date',
    ];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function authors(): HasMany
    {
        return $this->hasMany(Author::class);
    }
}
