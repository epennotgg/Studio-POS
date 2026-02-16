<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Users (Admin & Employee) - Login via Username & PIN
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('pin'); // Akan di-hash (bcrypt)
            $table->enum('role', ['admin', 'employee'])->default('employee');
            $table->rememberToken();
            $table->timestamps();
        });

        // 2. Categories (Bingkai, Cetak Foto, Aksesoris, dll)
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // 3. Products (Inventory)
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Misal: "Cetak 4R", "Bingkai A4 Gold"
            $table->string('type_color')->nullable(); // Variasi warna/tipe
            $table->string('barcode')->nullable()->unique(); // Opsional
            $table->integer('stock')->default(0);
            
            // Harga Beli & Jual Multi-Level
            $table->decimal('buy_price', 12, 2);
            $table->decimal('price_general', 12, 2);
            $table->decimal('price_agent1', 12, 2);
            $table->decimal('price_agent2', 12, 2);
            
            $table->timestamps();
        });

        // 4. Transactions (Header)
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id')->unique(); // Format: INV-YYYYMMDD-XXXX
            $table->foreignId('user_id')->constrained(); // Kasir yang menangani
            
            // Data Pelanggan
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->enum('customer_type', ['umum', 'agen1', 'agen2'])->default('umum');
            
            $table->string('payment_method'); // Cash, Transfer, QRIS
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', ['paid', 'pending', 'cancelled'])->default('paid');
            
            $table->timestamps();
        });

        // 5. Transaction Items (Detail Barang)
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained();
            
            $table->integer('quantity');
            $table->decimal('price_at_transaction', 12, 2); // Harga saat transaksi terjadi (snapshot)
            $table->decimal('subtotal', 12, 2);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('users');
    }
};