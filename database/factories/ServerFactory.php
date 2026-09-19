<?php

namespace Database\Factories;

use App\Enums\ServerType;
use App\Models\Company;
use App\Models\Server;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Server>
 */
class ServerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'created_by' => User::factory(),
            'company_id' => Company::factory(),
            'name' => fake()->unique()->word().'-server',
            'type' => fake()->randomElement(ServerType::cases()),
            'ip_address' => fake()->ipv4(),
        ];
    }
}
