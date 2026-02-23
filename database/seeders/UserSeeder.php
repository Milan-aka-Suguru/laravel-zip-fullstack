<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
class UserSeeder extends Seeder
{
    public function run()
    {
        User::factory()->create([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'asd',
        ]);
    }
} 

