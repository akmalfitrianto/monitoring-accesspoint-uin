<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AccessPoint;

class AccessPointSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AccessPoint::insert([
            [
                'building_id' => 1,
                'name' => 'AP-A1',
                'mac_address' => 'AA:BB:CC:DD:EE:01',
                'x_position' => 2,
                'y_position' => 4,
                'signal_strength' => -45,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'building_id' => 1,
                'name' => 'AP-A2',
                'mac_address' => 'AA:BB:CC:DD:EE:02',
                'x_position' => 7,
                'y_position' => 5,
                'signal_strength' => -60,
                'status' => 'offline',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'building_id' => 3,
                'name' => 'AP-A3',
                'mac_address' => 'AA:BB:CC:DD:EE:03',
                'x_position' => 5,
                'y_position' => 3,
                'signal_strength' => -50,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'building_id' => 1,
                'name' => 'AP-A4',
                'mac_address' => 'AA:BB:CC:DD:EE:04',
                'x_position' => 7,
                'y_position' => 3,
                'signal_strength' => -66,
                'status' => 'maintenance',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
