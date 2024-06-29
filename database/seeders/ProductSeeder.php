<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Array nama produk makanan Indonesia
        $namaProdukMakanan = [
            "Nasi Goreng",
            "Sate Ayam",
            "Rendang",
            "Gado-Gado",
            "Bakso",
            "Mie Goreng",
            "Soto Ayam",
            "Ayam Penyet",
            "Pecel Lele",
            "Martabak Manis"
        ];

        // Array harga produk makanan dalam integer (dalam ribuan rupiah)
        $hargaProdukMakanan = [
            25000, // Nasi Goreng
            20000, // Sate Ayam
            50000, // Rendang
            15000, // Gado-Gado
            18000, // Bakso
            22000, // Mie Goreng
            25000, // Soto Ayam
            30000, // Ayam Penyet
            20000, // Pecel Lele
            35000  // Martabak Manis
        ];

        // Array deskripsi produk makanan singkat
        $deskripsiProdukMakanan = [
            "Nasi goreng dengan bumbu khas Indonesia.",
            "Sate ayam dengan bumbu kacang yang lezat.",
            "Daging sapi dimasak dengan rempah khas Padang.",
            "Sayuran rebus dengan saus kacang.",
            "Bakso sapi dengan kuah gurih.",
            "Mie goreng dengan sayuran dan telur.",
            "Sup ayam dengan rempah kuning yang segar.",
            "Ayam goreng dengan sambal pedas.",
            "Lele goreng dengan sambal dan lalapan.",
            "Martabak manis dengan topping beragam."
        ];

        $estimasi = [
            '⚡',
            '5 min',
            '10 min',
            '15 min',
            '20 min',
        ];

        // Menampilkan data produk
        for ($i = 0; $i < count($namaProdukMakanan); $i++) {
            Product::create([
                'category_id' => random_int(1, 8),
                'merchant_id' => random_int(1, 5),
                'name' => $namaProdukMakanan[$i],
                'price' => $hargaProdukMakanan[$i],
                'description' => $deskripsiProdukMakanan[$i],
                'estimate' => $estimasi[random_int(0, 4)],
                'active' => random_int(0, 1)
            ]);
        }
    }
}
