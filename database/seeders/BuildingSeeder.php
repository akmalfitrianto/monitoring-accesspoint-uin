<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Building;

class BuildingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        building::insert([
            [
                'name' => 'gedung A',
                'code' => 'A',
                'description' => 'gedung ruang kelas',
                'grid_width' => 10,
                'grid_height' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'gedung B',
                'code' => 'B',
                'description' => 'gedung ruang kelas',
                'grid_width' => 12,
                'grid_height' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'gedung PSB',
                'code' => 'PSB',
                'description' => 'gedung fakultas',
                'grid_width' => 10,
                'grid_height' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
