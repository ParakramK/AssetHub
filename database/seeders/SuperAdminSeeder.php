<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('is_super_admin', true)->firstOrFail();

        $input = $this->command->getInput();

        $name = $this->command->option('name')
            ?? env('SUPER_ADMIN_NAME');

        $email = $this->command->option('email')
            ?? env('SUPER_ADMIN_EMAIL');

        $password = $this->command->option('password')
            ?? env('SUPER_ADMIN_PASSWORD');

        if (! $name && $input->isInteractive()) {
            $name = $this->command->ask('Super Admin name');
        }

        if (! $email && $input->isInteractive()) {
            $email = $this->command->ask('Super Admin email');
        }

        if (! $password && $input->isInteractive()) {
            $password = $this->command->secret('Super Admin password');
        }

        if (! $name || ! $email || ! $password) {
            throw new \RuntimeException(
                'Super Admin name, email and password are required.'
            );
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => $password,
                'role_id' => $role->id,
            ]
        );

        $this->command->info('Super Admin created successfully.');
    }
}
