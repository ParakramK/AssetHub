<?php

namespace Database\Factories;

use App\Models\Server;
use App\Models\SshKey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SshKey>
 */
class SshKeyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'server_id' => Server::factory(),
            'name' => fake()->unique()->word(),
            'public_key' => 'ssh-ed25519 '.fake()->regexify('[A-Za-z0-9+/]{64}'),
            'private_key' => 'secret-private-key',
        ];
    }
}
