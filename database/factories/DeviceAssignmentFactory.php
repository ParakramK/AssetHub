<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\DeviceAssignment;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeviceAssignment>
 */
class DeviceAssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $device = Device::factory()->create();

        return [
            'device_id' => $device->id,
            'employee_id' => Employee::factory()->create(['company_id' => $device->company_id])->id,
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
