<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['name' => 'kg', 'is_active' => true],
            ['name' => 'g', 'is_active' => true],
            ['name' => 'L', 'is_active' => true],
            ['name' => 'ml', 'is_active' => true],
            ['name' => 'Tonne', 'is_active' => true],
            ['name' => 'Quintal', 'is_active' => true],
            ['name' => 'Bag', 'is_active' => true],
            ['name' => 'Packet', 'is_active' => true],
            ['name' => 'Bottle', 'is_active' => true],
            ['name' => 'Drum', 'is_active' => true],
            ['name' => 'Pouch', 'is_active' => true],
            ['name' => 'Piece', 'is_active' => true],
            ['name' => 'Pack/Bundle', 'is_active' => true],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['name' => $unit['name']], $unit);
        }
    }
}
