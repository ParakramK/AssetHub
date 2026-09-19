<?php

namespace Database\Factories;

use App\Enums\DeviceStatus;
use App\Models\Company;
use App\Models\Device;
use App\Models\DeviceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Device>
 */
class DeviceFactory extends Factory
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
            'device_type_id' => DeviceType::factory(),
            'brand' => fake()->randomElement(['Apple', 'Dell', 'Lenovo', 'HP', 'Samsung', 'Logitech']),
            'model' => fake()->word(),
            'imei' => fake()->optional()->numerify('###############'),
            'mac_address' => fake()->optional()->macAddress(),
            'serial_no' => fake()->optional()->bothify('??########'),
            'status' => DeviceStatus::Available,
        ];
    }
}
