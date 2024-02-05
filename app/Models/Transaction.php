<?php

namespace App\Models;

use App\Enums\BorrowedStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'user_id',
        'borrowed_date',
        'borrowed_for',
        'returned_date',
        'status',
        'fine',
    ];

    protected $casts = [
        'status' => BorrowedStatus::class,
        'borrowed_date' => 'date',
        'returned_date' => 'date',
        'fine' => 'integer',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function booted(): void
    {
        static::saving(function ($transaction) {
            $borrowedDate = Carbon::parse($transaction->borrowed_date);
            $borrowedFor = $transaction->borrowed_for;
            $returnDate = Carbon::parse($transaction->returned_date);
            $dueDate = $borrowedDate->addDays($borrowedFor);
            $delay = 0;
            $fine = 0;
            if ($returnDate->gt($dueDate)) {
                $delay = $dueDate->diffInDays($returnDate);
                $fine = $delay * 10;
            }
            $transaction->fine = $fine;
        });

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
