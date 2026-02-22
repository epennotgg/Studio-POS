<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Booking;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat User (Admin & Kasir) jika belum ada
        // Login menggunakan Username & PIN
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin',
                'pin' => Hash::make('123456'), // PIN Default
                'role' => 'admin',
            ]
        );

        $kasir = User::firstOrCreate(
            ['username' => 'kasir'],
            [
                'name' => 'Staff Kasir 1',
                'pin' => Hash::make('123456'),
                'role' => 'employee',
            ]
        );

        // 2. Buat Kategori sesuai dengan migrasi terbaru jika belum ada
        $catJasaCetak = Category::firstOrCreate(['name' => 'Jasa cetak']);
        $catBingkai = Category::firstOrCreate(['name' => 'Bingkai']);
        $catAlbum = Category::firstOrCreate(['name' => 'Album']);
        $catJasaFotoEdit = Category::firstOrCreate(['name' => 'Jasa foto, edit, dan pemasangan']);
        $catPenyimpanan = Category::firstOrCreate(['name' => 'Penyimpanan (FD/SDC)']);
        $catBahanCetak = Category::firstOrCreate(['name' => 'Bahan cetak']);
        $catLainnya = Category::firstOrCreate(['name' => 'Lainnya']);

        // 3. Buat Produk Dummy yang kompatibel dengan semua backend jika belum ada
        
        // A. Produk Jasa (Stok dibuat banyak/unlimited)
        $produkCetak4R = Product::firstOrCreate(
            ['name' => 'Cetak 4R Glossy'],
            [
                'category_id' => $catJasaCetak->id,
                'type_color' => 'Glossy',
                'stock' => 9999,
                'buy_price' => 1000,
                'price_general' => 3000, // Harga Umum
                'price_agent1' => 2500,  // Harga Agen 1
                'price_agent2' => 2200,  // Harga Agen 2
            ]
        );

        $produkPasFoto = Product::firstOrCreate(
            ['name' => 'Pas Foto (Paket 4 Lembar)'],
            [
                'category_id' => $catJasaCetak->id,
                'type_color' => 'Matte',
                'stock' => 9999,
                'buy_price' => 5000,
                'price_general' => 20000,
                'price_agent1' => 18000,
                'price_agent2' => 15000,
            ]
        );

        // B. Produk Jasa Foto, Edit, dan Pemasangan
        $produkJasaFoto = Product::firstOrCreate(
            ['name' => 'Jasa Foto Studio Indoor'],
            [
                'category_id' => $catJasaFotoEdit->id,
                'type_color' => 'Paket 1 Jam',
                'stock' => 9999,
                'buy_price' => 50000,
                'price_general' => 150000,
                'price_agent1' => 130000,
                'price_agent2' => 120000,
            ]
        );

        // C. Produk Fisik (Stok Terbatas)
        $produkBingkaiA4 = Product::firstOrCreate(
            ['name' => 'Bingkai A4 Minimalis'],
            [
                'category_id' => $catBingkai->id,
                'type_color' => 'Hitam',
                'barcode' => '8990001',
                'stock' => 50,
                'buy_price' => 15000,
                'price_general' => 35000,
                'price_agent1' => 30000,
                'price_agent2' => 28000,
            ]
        );

        $produkAlbum4R = Product::firstOrCreate(
            ['name' => 'Album Foto 4R (100 Slot)'],
            [
                'category_id' => $catAlbum->id,
                'type_color' => 'Motif Bunga',
                'barcode' => '8990002',
                'stock' => 20,
                'buy_price' => 25000,
                'price_general' => 50000,
                'price_agent1' => 45000,
                'price_agent2' => 40000,
            ]
        );

        // D. Produk Penyimpanan
        $produkFlashdisk = Product::firstOrCreate(
            ['name' => 'Flashdisk 32GB'],
            [
                'category_id' => $catPenyimpanan->id,
                'type_color' => 'USB 3.0',
                'barcode' => '8990003',
                'stock' => 30,
                'buy_price' => 40000,
                'price_general' => 75000,
                'price_agent1' => 65000,
                'price_agent2' => 60000,
            ]
        );

        // E. Produk Bahan Cetak
        $produkKertasFoto = Product::firstOrCreate(
            ['name' => 'Kertas Foto Glossy A4'],
            [
                'category_id' => $catBahanCetak->id,
                'type_color' => '100 Lembar',
                'barcode' => '8990004',
                'stock' => 100,
                'buy_price' => 50000,
                'price_general' => 85000,
                'price_agent1' => 75000,
                'price_agent2' => 70000,
            ]
        );

        // F. Produk Lainnya
        $produkBaterai = Product::firstOrCreate(
            ['name' => 'Baterai Alkaline AA'],
            [
                'category_id' => $catLainnya->id,
                'stock' => 100,
                'buy_price' => 5000,
                'price_general' => 10000,
                'price_agent1' => 9000,
                'price_agent2' => 8500,
            ]
        );

        // 4. Buat Transaksi Dummy (untuk testing riwayat transaksi)
        $this->createSampleTransactions($admin, $kasir, [
            $produkCetak4R,
            $produkBingkaiA4,
            $produkAlbum4R,
            $produkFlashdisk
        ]);

        // 5. Buat Booking Dummy (untuk testing booking system)
        $this->createSampleBookings($kasir);
    }

    private function createSampleTransactions($admin, $kasir, $products)
    {
        // Transaksi 1: Lunas (Cash)
        $transaction1 = Transaction::firstOrCreate(
            ['invoice_id' => 'INV-' . date('Ymd') . '-0001'],
            [
                'user_id' => $kasir->id,
                'customer_name' => 'Budi Santoso',
                'customer_phone' => '081234567890',
                'customer_type' => 'umum',
                'payment_method' => 'Cash',
                'total_amount' => 85000,
                'down_payment' => 0,
                'remaining_amount' => 0,
                'status' => 'paid',
            ]
        );

        TransactionItem::firstOrCreate(
            [
                'transaction_id' => $transaction1->id,
                'product_id' => $products[0]->id,
            ],
            [
                'quantity' => 10,
                'price_at_transaction' => $products[0]->price_general,
                'subtotal' => 10 * $products[0]->price_general,
            ]
        );

        TransactionItem::firstOrCreate(
            [
                'transaction_id' => $transaction1->id,
                'product_id' => $products[1]->id,
            ],
            [
                'quantity' => 1,
                'price_at_transaction' => $products[1]->price_general,
                'subtotal' => 1 * $products[1]->price_general,
            ]
        );

        // Transaksi 2: Pending dengan DP (Transfer)
        $transaction2 = Transaction::firstOrCreate(
            ['invoice_id' => 'INV-' . date('Ymd') . '-0002'],
            [
                'user_id' => $kasir->id,
                'customer_name' => 'Siti Rahayu',
                'customer_phone' => '081298765432',
                'customer_type' => 'agen1',
                'payment_method' => 'Transfer',
                'total_amount' => 175000,
                'down_payment' => 50000,
                'remaining_amount' => 125000,
                'status' => 'pending',
            ]
        );

        TransactionItem::firstOrCreate(
            [
                'transaction_id' => $transaction2->id,
                'product_id' => $products[2]->id,
            ],
            [
                'quantity' => 2,
                'price_at_transaction' => $products[2]->price_agent1,
                'subtotal' => 2 * $products[2]->price_agent1,
            ]
        );

        TransactionItem::firstOrCreate(
            [
                'transaction_id' => $transaction2->id,
                'product_id' => $products[3]->id,
            ],
            [
                'quantity' => 1,
                'price_at_transaction' => $products[3]->price_agent1,
                'subtotal' => 1 * $products[3]->price_agent1,
            ]
        );

        // Transaksi 3: Lunas (QRIS)
        $transaction3 = Transaction::firstOrCreate(
            ['invoice_id' => 'INV-' . date('Ymd') . '-0003'],
            [
                'user_id' => $admin->id,
                'customer_name' => 'Ahmad Fauzi',
                'customer_phone' => '081312345678',
                'customer_type' => 'agen2',
                'payment_method' => 'QRIS',
                'total_amount' => 120000,
                'down_payment' => 0,
                'remaining_amount' => 0,
                'status' => 'paid',
            ]
        );

        TransactionItem::firstOrCreate(
            [
                'transaction_id' => $transaction3->id,
                'product_id' => $products[0]->id,
            ],
            [
                'quantity' => 20,
                'price_at_transaction' => $products[0]->price_agent2,
                'subtotal' => 20 * $products[0]->price_agent2,
            ]
        );

        TransactionItem::firstOrCreate(
            [
                'transaction_id' => $transaction3->id,
                'product_id' => $products[1]->id,
            ],
            [
                'quantity' => 2,
                'price_at_transaction' => $products[1]->price_agent2,
                'subtotal' => 2 * $products[1]->price_agent2,
            ]
        );
    }

    private function createSampleBookings($kasir)
    {
        // Booking 1: Pending
        Booking::firstOrCreate(
            ['booking_code' => 'BOOK-' . date('Ymd') . '-0001'],
            [
                'user_id' => $kasir->id,
                'customer_name' => 'Rina Melati',
                'customer_phone' => '081511223344',
                'studio_category' => 'family_graduation',
                'package_type' => 'Paket Keluarga',
                'package_price' => 500000,
                'booking_date' => now()->addDays(7),
                'number_of_people' => 5,
                'down_payment' => 100000,
                'total_amount' => 500000,
                'remaining_amount' => 400000,
                'notes' => 'Booking untuk foto keluarga wisuda',
                'status' => 'pending',
                'transaction_id' => null,
            ]
        );

        // Booking 2: Confirmed
        Booking::firstOrCreate(
            ['booking_code' => 'BOOK-' . date('Ymd') . '-0002'],
            [
                'user_id' => $kasir->id,
                'customer_name' => 'Dewi Anggraini',
                'customer_phone' => '081622334455',
                'studio_category' => 'prewedding_indoor',
                'package_type' => 'Paket Prewedding Premium',
                'package_price' => 1500000,
                'booking_date' => now()->addDays(14),
                'number_of_people' => 2,
                'down_payment' => 500000,
                'total_amount' => 1500000,
                'remaining_amount' => 1000000,
                'notes' => 'Include 3 set baju dan makeup',
                'status' => 'confirmed',
                'transaction_id' => null,
            ]
        );

        // Booking 3: Completed
        Booking::firstOrCreate(
            ['booking_code' => 'BOOK-' . date('Ymd') . '-0003'],
            [
                'user_id' => $kasir->id,
                'customer_name' => 'Hendra Wijaya',
                'customer_phone' => '081733445566',
                'studio_category' => 'studio_outdoor',
                'package_type' => 'Paket Outdoor Basic',
                'package_price' => 800000,
                'booking_date' => now()->subDays(3),
                'number_of_people' => 4,
                'down_payment' => 200000,
                'total_amount' => 800000,
                'remaining_amount' => 600000,
                'notes' => 'Lokasi taman kota',
                'status' => 'completed',
                'transaction_id' => null,
            ]
        );
    }
}
