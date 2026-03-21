<?php

namespace App\Models;

use App\Enums\BorrowedStatus;
use App\Observers\TransactionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property-read User|null $user
 * @property-read Book|null $book
 * @property string|null $status
 */
#[ObservedBy(TransactionObserver::class)]
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
        parent::boot();

        static::saving(function ($transaction): void {
            $borrowedDate = Carbon::parse($transaction->borrowed_date);
            $borrowedFor = (int) $transaction->borrowed_for;
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
    }
}
