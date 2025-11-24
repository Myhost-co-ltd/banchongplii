<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = ['superadmin', 'teacher', 'director'];
        foreach ($roles as $name) {
            Role::firstOrCreate(['name' => $name]);
        }
    }
}
