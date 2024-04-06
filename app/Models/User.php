<?php

namespace App\Models;

use App\Observers\UserObserver;
use Filament\AvatarProviders\UiAvatarsProvider;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

#[ObservedBy(UserObserver::class)]
class User extends Authenticatable implements FilamentUser, HasAvatar, MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'status',
        'address',
        'phone',
        'avatar_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'status' => 'boolean',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        $role = auth()->user()->role->name;

        return match ($panel->getId()) {
            'admin' => $role == 'admin',
            'staff' => $role == 'staff',
            default => false,
        } && $this->hasVerifiedEmail();
    }

    public function getFilamentAvatarUrl(): ?string
    {
        $uiAvatarsProvider = new UiAvatarsProvider();

        if ($this->avatar_url) {
            return Storage::url($this->avatar_url);
        }

        // If avatar_url is not available, use the UiAvatarsProvider directly
        return $uiAvatarsProvider->get($this);
    }

    public static function booted(): void
    {
        parent::boot();

        static::created(function ($model) {
            $cacheKey = 'NavigationCount_'.class_basename($model).$model->getTable();
            if (Cache::has($cacheKey)) {
                Cache::forget($cacheKey);
            }
            $borrowerKey = 'BorrowerCount_'.class_basename($model).$model->getTable();
            if (Cache::has($borrowerKey)) {
                Cache::forget($borrowerKey);
            }
        });

        static::deleted(function ($model) {
            $cacheKey = 'NavigationCount_'.class_basename($model).$model->getTable();
            if (Cache::has($cacheKey)) {
                Cache::forget($cacheKey);
            }
            $borrowerKey = 'BorrowerCount_'.class_basename($model).$model->getTable();
            if (Cache::has($borrowerKey)) {
                Cache::forget($borrowerKey);
            }
        });
    }
}
