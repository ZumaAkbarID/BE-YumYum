<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lists = ['Food', 'Drink', 'Junk', 'Meat', 'Noodle', 'Fresh', 'SeaFood', 'Fried'];

        foreach ($lists as $key => $value) {
            Category::create([
                'name' => $value,
                'description' => $value . '-' . $key
            ]);
        }
    }
}
