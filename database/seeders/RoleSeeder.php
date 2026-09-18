<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate([
            'name' => 'Super Admin',
            'description' => 'Super Admin role',
            'is_super_admin' => true,
        ]);

        Role::firstOrCreate([
            'name' => 'Admin',
            'description' => 'Standard Admin role',
        ]);

        Role::firstOrCreate([
            'name' => 'User',
            'description' => 'Standard User role',
        ]);

      
    }
}
