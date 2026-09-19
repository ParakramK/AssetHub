<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Domain;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Domain>
 */
class DomainFactory extends Factory
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
            'created_by' => User::factory(),
            'domain_name' => fake()->unique()->domainName(),
            'registrar' => fake()->randomElement(['GoDaddy', 'Namecheap', 'Cloudflare', 'Porkbun', 'Google Domains']),
            'expiry_date' => fake()->dateTimeBetween('+1 month', '+3 years')->format('Y-m-d'),
        ];
    }
}
