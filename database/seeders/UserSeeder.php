<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->role('admin')->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => 'developer',
        ]);

        User::factory()->role('staff')->create([
            'name' => 'Catharine McCall',
            'email' => 'catherine@gmail.com',
            'password' => 'staff001',
        ]);

        User::factory()->role('staff')->create([
            'name' => 'Lina Carter',
            'email' => 'lina@gmail.com',
            'password' => 'staff002',
        ]);

        User::factory(7)->role('borrower')->create();
    }
}
