<?php

namespace Database\Factories;

use App\Enums\BorrowedStatus;
use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'borrowed_date' => fake()->dateTimeThisYear(),
            'borrowed_for' => fake()->numberBetween(1, 30),
            'returned_date' => fake()->dateTimeThisYear(),
            'status' => BorrowedStatus::randomValue(),
            'fine' => fake()->numberBetween(0, 1000)
        ];
    }
}
