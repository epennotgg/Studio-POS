<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat User (Admin & Kasir)
        // Login menggunakan Username & PIN
        User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'pin' => Hash::make('123456'), // PIN Default
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Staff Kasir 1',
            'username' => 'kasir',
            'pin' => Hash::make('123456'),
            'role' => 'employee',
        ]);

        // 2. Buat Kategori
        $catCetak = Category::create(['name' => 'Jasa Foto dan Cetak']);
        $catBingkai = Category::create(['name' => 'Bingkai & Album']);
        $catAcc = Category::create(['name' => 'Aksesoris & Perlengkapan']);

        // 3. Buat Produk Dummy
        
        // A. Produk Jasa (Stok dibuat banyak/unlimited)
        Product::create([
            'category_id' => $catCetak->id,
            'name' => 'Cetak 4R Glossy',
            'type_color' => 'Glossy',
            'stock' => 9999,
            'buy_price' => 1000,
            'price_general' => 3000, // Harga Umum
            'price_agent1' => 2500,  // Harga Agen 1
            'price_agent2' => 2200,  // Harga Agen 2
        ]);

        Product::create([
            'category_id' => $catCetak->id,
            'name' => 'Pas Foto (Paket 4 Lembar)',
            'type_color' => 'Matte',
            'stock' => 9999,
            'buy_price' => 5000,
            'price_general' => 20000,
            'price_agent1' => 18000,
            'price_agent2' => 15000,
        ]);

        // B. Produk Fisik (Stok Terbatas)
        Product::create([
            'category_id' => $catBingkai->id,
            'name' => 'Bingkai A4 Minimalis',
            'type_color' => 'Hitam',
            'barcode' => '8990001',
            'stock' => 50,
            'buy_price' => 15000,
            'price_general' => 35000,
            'price_agent1' => 30000,
            'price_agent2' => 28000,
        ]);

        Product::create([
            'category_id' => $catBingkai->id,
            'name' => 'Album Foto 4R (100 Slot)',
            'type_color' => 'Motif Bunga',
            'barcode' => '8990002',
            'stock' => 20,
            'buy_price' => 25000,
            'price_general' => 50000,
            'price_agent1' => 45000,
            'price_agent2' => 40000,
        ]);

        Product::create([
            'category_id' => $catAcc->id,
            'name' => 'Baterai Alkaline AA',
            'stock' => 100,
            'buy_price' => 5000,
            'price_general' => 10000,
            'price_agent1' => 9000,
            'price_agent2' => 8500,
        ]);
    }
}