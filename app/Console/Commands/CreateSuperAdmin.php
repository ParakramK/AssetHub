<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateSuperAdmin extends Command
{
    protected $signature = 'super-admin:create
                            {--name= : Super Admin name}
                            {--email= : Super Admin email}
                            {--password= : Super Admin password}';

    protected $description = 'Create or update the Super Admin user';

    public function handle(): int
    {
        $role = Role::where('is_super_admin', true)->first();

        if (! $role) {
            $this->error('No Super Admin role exists.');

            return self::FAILURE;
        }

        $name = $this->option('name');
        $email = $this->option('email');
        $password = $this->option('password');

        if (! $name) {
            $name = $this->ask('Super Admin name');
        }

        if (! $email) {
            $email = $this->ask('Super Admin email');
        }

        if (! $password) {
            $password = $this->secret('Super Admin password');
        }

        if (! $name || ! $email || ! $password) {
            $this->error('Name, email and password are required.');

            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
            ]
        );

        $user->syncRoles([$role]);

        $this->info('Super Admin created successfully.');

        return self::SUCCESS;
    }
}
