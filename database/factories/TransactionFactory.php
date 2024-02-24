<?php

namespace Database\Factories;

use App\Enums\BorrowedStatus;
use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'book_id' => Book::factory(),
            'user_id' => User::factory(),
            'borrowed_date' => now(),
            'borrowed_for' => fake()->numberBetween(1, 30),
            'returned_date' => function (array $attributes) {
                $isNull = fake()->boolean(30);

                if ($isNull) {

                    return null;
                } elseif ($attributes['borrowed_for'] >= 1) {
                    $borrowedDate = Carbon::parse($attributes['borrowed_date']);
                    $borrowedFor = $attributes['borrowed_for'];

                    $maxReturnDate = $borrowedDate->copy()->addDays($borrowedFor - 1);
                    $returnedDate = Carbon::parse(fake()->dateTimeBetween($borrowedDate, $maxReturnDate));

                    return $returnedDate;
                }
            },
            'status' => function (array $attributes) {
                $returnedDate = $attributes['returned_date'];

                if ($returnedDate === null) {
                    return BorrowedStatus::Borrowed;
                } elseif (Carbon::parse($returnedDate)->isPast()) {
                    return BorrowedStatus::Delayed;
                } else {
                    return BorrowedStatus::Returned;
                }
            },
            'fine' => null,
        ];
    }
}
