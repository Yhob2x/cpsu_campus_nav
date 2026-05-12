<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'full_name' => 'Administrator',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]
        );
        
        $this->command->info('Admin user created/updated successfully!');
        $this->command->info('Username: admin');
        $this->command->info('Password: admin123');
    }
}