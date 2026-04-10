<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['id_category' => 'CAT-001', 'category_name' => 'Suspension',        'description' => 'Shock Absorber, Rack End, Ball Joint, Tie Rod'],
            ['id_category' => 'CAT-002', 'category_name' => 'Brakes',            'description' => 'Disc Pad, Brake Drum, Brake Shoe'],
            ['id_category' => 'CAT-003', 'category_name' => 'Filter',            'description' => 'Oil Filter, Air Filter, Fuel Filter'],
            ['id_category' => 'CAT-004', 'category_name' => 'Spark Plug',        'description' => 'Busi standard dan iridium'],
            ['id_category' => 'CAT-005', 'category_name' => 'Bearing',           'description' => 'Wheel Bearing, Hub Bearing'],
            ['id_category' => 'CAT-006', 'category_name' => 'Clutch',            'description' => 'Clutch Cover, Disc, Bearing'],
            ['id_category' => 'CAT-007', 'category_name' => 'Belt',              'description' => 'Timing Belt, V-Belt, Serpentine Belt'],
            ['id_category' => 'CAT-008', 'category_name' => 'Oil Seal',          'description' => 'Crankshaft Seal, Camshaft Seal'],
            ['id_category' => 'CAT-009', 'category_name' => 'Aki / Battery',     'description' => 'Baterai kendaraan'],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->updateOrInsert(['id_category' => $cat['id_category']], array_merge($cat, [
                'created_at' => now(), 'updated_at' => now(),
            ]));
        }
    }
}
