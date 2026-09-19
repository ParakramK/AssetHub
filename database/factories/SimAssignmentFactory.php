<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\SimAssignment;
use App\Models\SimCard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SimAssignment>
 */
class SimAssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $simCard = SimCard::factory()->create();

        return [
            'sim_card_id' => $simCard->id,
            'employee_id' => Employee::factory()->create(['company_id' => $simCard->company_id])->id,
            'assigned_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'returned_at' => null,
        ];
    }

    public function returned(): static
    {
        return $this->state(fn (array $attributes) => [
            'returned_at' => fake()->dateTimeBetween($attributes['assigned_at'], 'now'),
        ]);
    }
}
