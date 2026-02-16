<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to users table
        Schema::table('users', function (Blueprint $table) {
            $table->index('username');
            $table->index('role');
            $table->index(['role', 'created_at']);
        });

        // Add indexes to products table
        Schema::table('products', function (Blueprint $table) {
            $table->index('category_id');
            $table->index('stock');
            $table->index('name');
            $table->index(['category_id', 'stock']);
            $table->index(['stock', 'created_at']);
        });

        // Add indexes to transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('invoice_id');
            $table->index('status');
            $table->index('customer_type');
            $table->index('created_at');
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['customer_type', 'created_at']);
        });

        // Add indexes to transaction_items table
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->index('transaction_id');
            $table->index('product_id');
            $table->index(['transaction_id', 'product_id']);
        });

        // Add indexes to categories table
        Schema::table('categories', function (Blueprint $table) {
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['username']);
            $table->dropIndex(['role']);
            $table->dropIndex(['role', 'created_at']);
        });

        // Remove indexes from products table
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['stock']);
            $table->dropIndex(['name']);
            $table->dropIndex(['category_id', 'stock']);
            $table->dropIndex(['stock', 'created_at']);
        });

        // Remove indexes from transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['invoice_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['customer_type']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['customer_type', 'created_at']);
        });

        // Remove indexes from transaction_items table
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropIndex(['transaction_id']);
            $table->dropIndex(['product_id']);
            $table->dropIndex(['transaction_id', 'product_id']);
        });

        // Remove indexes from categories table
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });
    }
};