<?php

namespace Database\Factories;

use App\Enums\SimProvider;
use App\Enums\SimStatus;
use App\Models\Company;
use App\Models\SimCard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SimCard>
 */
class SimCardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'provider' => fake()->randomElement(SimProvider::cases()),
            'number' => fake()->unique()->numerify('+977-98########'),
            'present_status' => fake()->randomElement(SimStatus::cases()),
        ];
    }
}
