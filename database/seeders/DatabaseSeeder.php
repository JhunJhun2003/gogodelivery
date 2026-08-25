<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'email' => 'admin@gogodelivery.test',
                'password' => 'admin123',
                'role' => User::ROLE_ADMIN,
            ]
        );

        User::updateOrCreate(
            ['username' => 'shop1'],
            [
                'name' => 'Shop Owner',
                'email' => 'shop@gogodelivery.test',
                'password' => 'shop123',
                'role' => User::ROLE_SHOP,
            ]
        );

        User::updateOrCreate(
            ['username' => 'biker1'],
            [
                'name' => 'Biker Rider',
                'email' => 'biker@gogodelivery.test',
                'password' => 'biker123',
                'role' => User::ROLE_BIKER,
            ]
        );
    }
}
