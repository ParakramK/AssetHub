<?php

namespace Database\Factories;

use App\Models\Server;
use App\Models\ServerCredential;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServerCredential>
 */
class ServerCredentialFactory extends Factory
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
            'username' => fake()->unique()->userName(),
            'password' => 'secret-password',
        ];
    }
}
