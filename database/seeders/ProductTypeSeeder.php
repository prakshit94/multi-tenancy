<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductType;

class ProductTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Seeds', 'is_active' => true],
            ['name' => 'Fertilizers', 'is_active' => true],
            ['name' => 'Pesticides', 'is_active' => true],
            ['name' => 'Insecticides', 'is_active' => true],
            ['name' => 'Fungicides', 'is_active' => true],
            ['name' => 'Herbicides', 'is_active' => true],
            ['name' => 'Plant Growth Regulators (PGR)', 'is_active' => true],
            ['name' => 'Bio-Fertilizers', 'is_active' => true],
            ['name' => 'Implements/Tools', 'is_active' => true],
            ['name' => 'Irrigation Equipment', 'is_active' => true],
            ['name' => 'Feed', 'is_active' => true],
            ['name' => 'Simple', 'is_active' => true], // Legacy/default compatible
            ['name' => 'Variable', 'is_active' => true], // Legacy/default compatible
        ];

        foreach ($types as $type) {
            ProductType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
