<?php

namespace Database\Seeders;

use App\Models\JerseySize;
use Illuminate\Database\Seeder;

class JerseySizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sizes = [
            ['name' => 'Extra Small', 'code' => 'xs', 'sort_order' => 1],
            ['name' => 'Small', 'code' => 's', 'sort_order' => 2],
            ['name' => 'Medium', 'code' => 'm', 'sort_order' => 3],
            ['name' => 'Large', 'code' => 'l', 'sort_order' => 4],
            ['name' => 'Extra Large', 'code' => 'xl', 'sort_order' => 5],
            ['name' => 'Double Extra Large', 'code' => 'xxl', 'sort_order' => 6],
            ['name' => 'Triple Extra Large', 'code' => 'xxxl', 'sort_order' => 7],
        ];

        foreach ($sizes as $size) {
            JerseySize::firstOrCreate(
                ['code' => $size['code']],
                [
                    'name' => $size['name'],
                    'sort_order' => $size['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
