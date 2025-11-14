<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('12345678'),
            'role_id' => 1
        ]);

        User::create([
            'name' => 'ครูทดสอบ',
            'email' => 'teacher@gmail.com',
            'password' => Hash::make('12345678'),
            'role_id' => 2
        ]);

        User::create([
            'name' => 'ผอ.โรงเรียน',
            'email' => 'director@gmail.com',
            'password' => Hash::make('12345678'),
            'role_id' => 3
        ]);
    }
}

