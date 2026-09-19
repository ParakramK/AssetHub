<?php

namespace Database\Seeders;

use App\Models\DeviceType;
use Illuminate\Database\Seeder;

class DeviceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Laptop', 'Desktop', 'Monitor', 'Mobile Phone', 'Tablet', 'Keyboard'] as $name) {
            DeviceType::firstOrCreate(['name' => $name]);
        }
    }
}
