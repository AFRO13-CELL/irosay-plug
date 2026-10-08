<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'iPhones',          'tracking_type' => 'serialized'], // IMEI-tracked, per spec section 8
            ['name' => 'Apple Watches',    'tracking_type' => 'quantity'],
            ['name' => 'iPads',            'tracking_type' => 'quantity'],
            ['name' => 'AirPods',          'tracking_type' => 'quantity'],
            ['name' => 'Chargers',         'tracking_type' => 'quantity'],
            ['name' => 'Networking',       'tracking_type' => 'quantity'], // CAT4 routers, pocket MiFi
            ['name' => 'Other Accessories','tracking_type' => 'quantity'], // e.g. Veyes reed diffuser
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                ['name' => $cat['name'], 'tracking_type' => $cat['tracking_type']]
            );
        }
    }
}
