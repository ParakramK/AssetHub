<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::permissions() as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }
    }

    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        $resources = ['users', 'roles', 'companies', 'domains'];
        $actions = ['view', 'create', 'update', 'delete'];

        $permissions = [];

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                $permissions[] = "{$resource}.{$action}";
            }
        }

        return $permissions;
    }
}
