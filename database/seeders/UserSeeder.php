<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create first user: mia59@gmail.com
        User::updateOrCreate(
            ['email' => 'mia59@gmail.com'],
            [
                'name' => 'MIA User',
                'password' => Hash::make('mia_password'),
                'email_verified_at' => now(),
            ]
        );

        // Create second user: aden@gmail.com
        User::updateOrCreate(
            ['email' => 'aden@gmail.com'],
            [
                'name' => 'Aden User',
                'password' => Hash::make('aden_password'),
                'email_verified_at' => now(),
            ]
        );
    }
}
