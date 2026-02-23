<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test',
                'password' => 'asd',
            ],
        );
    }
} 

