<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $fillable = [
        'name',
        'description',
    ];

    public const IS_ADMIN = 1;

    public const IS_STAFF = 2;

    public const IS_BORROWER = 3;

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
