<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Publisher;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
        ]);

        $admin = User::factory()->role('admin')->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => 'developer',
        ]);

        $staffs = User::factory(2)->role('staff')
            ->state(new Sequence(
                [
                    'name' => 'Catharine McCall',
                    'email' => 'catherine@gmail.com',
                    'password' => 'staff001',
                ],
                [
                    'name' => 'Lina Carter',
                    'email' => 'lina@gmail.com',
                    'password' => 'staff002',
                ],
            ))->create();

        $users = User::factory(7)->role('borrower')
            ->create();

        $publishers = Publisher::factory(10)->create();

        $authors = Author::factory(10)->recycle($publishers)->create();

        $genres = Genre::factory(10)->create();

        $books = Book::factory(10)
            ->recycle($publishers)
            ->recycle($authors)
            ->recycle($genres)
            ->create();

        $transaction = Transaction::factory(10)
            ->recycle($users)
            ->recycle($books)
            ->create();
    }
}
