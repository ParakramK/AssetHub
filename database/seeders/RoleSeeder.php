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
            'guard_name' => 'web',
        ], [
            'description' => 'Super Admin role',
            'is_super_admin' => true,
        ]);

        $admin = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ], [
            'description' => 'Standard Admin role',
        ]);

        Role::firstOrCreate([
            'name' => 'User',
            'guard_name' => 'web',
        ], [
            'description' => 'Standard User role',
        ]);

        $admin->syncPermissions([
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'companies.view',
            'companies.create',
            'companies.update',
            'companies.delete',
            'domains.view',
            'domains.create',
            'domains.update',
            'domains.delete',
        ]);
    }
}
