<?php

namespace Database\Seeders;

use App\Models\Merchant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MerchantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 5; $i++) {
            Merchant::create([
                'username' => fake()->userName(),
                'name' => fake()->name(),
                'password' => Hash::make('passw'),
                'description' => 'Jualan makanan, enak pokoke jos',
                'is_open' => random_int(0, 1),
                'device_id' => fake()->sha1(),
            ]);
        }
    }
}
