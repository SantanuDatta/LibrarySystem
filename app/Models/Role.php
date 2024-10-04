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

    const IS_ADMIN = 'admin';

    const IS_STAFF = 'staff';

    const IS_BORROWER = 'borrower';

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
