<?php

namespace Database\Seeders;

use App\Enums\PermissionName;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (PermissionName::cases() as $case) {
            Permission::firstOrCreate([
                'name' => $case->value,
                'guard_name' => 'web',
            ]);
        }
    }
}
