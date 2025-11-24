<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $roles = Role::pluck('id', 'name');

        User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('12345678'),
                'role_id' => $roles['superadmin'] ?? null,
            ]
        );

        User::firstOrCreate(
            ['email' => 'teacher@gmail.com'],
            [
                'name' => 'ครูทดสอบ',
                'password' => Hash::make('12345678'),
                'role_id' => $roles['teacher'] ?? null,
            ]
        );

        User::firstOrCreate(
            ['email' => 'director@gmail.com'],
            [
                'name' => 'ผอ.โรงเรียน',
                'password' => Hash::make('12345678'),
                'role_id' => $roles['director'] ?? null,
            ]
        );
    }
}
