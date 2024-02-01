<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::factory(3)
            ->state(new Sequence(
                ['name' => 'admin', 'description' => 'Admin Privilege'],
                ['name' => 'staff', 'description' => 'Staff Privilege'],
                ['name' => 'borrower', 'description' => 'Borrower Privilege'],
            ))->create();
    }
}
