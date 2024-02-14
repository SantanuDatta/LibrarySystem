<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Genre;
use App\Models\Publisher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'author_id' => Author::factory(),
            'publisher_id' => Publisher::factory(),
            'genre_id' => Genre::factory(),
            'title' => fake()->name(),
            'isbn' => fake()->unique()->isbn13(),
            'price' => fake()->randomFloat(2, 0, 100),
            'description' => fake()->realText(600),
            'stock' => fake()->numberBetween(0, 100),
            'available' => fake()->boolean(50),
            'published' => fake()->dateTimeThisCentury(),
        ];
    }
}
