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
        // Add indexes for performance optimization - check if they exist first
        Schema::table('transactions', function (Blueprint $table) {
            // Composite index for common query patterns
            if (!Schema::hasIndex('transactions', ['created_at', 'status'])) {
                $table->index(['created_at', 'status']);
            }
            if (!Schema::hasIndex('transactions', ['user_id', 'created_at'])) {
                $table->index(['user_id', 'created_at']);
            }
            if (!Schema::hasIndex('transactions', ['customer_type', 'created_at'])) {
                $table->index(['customer_type', 'created_at']);
            }
        });

        Schema::table('products', function (Blueprint $table) {
            // Index for stock filtering
            if (!Schema::hasIndex('products', ['stock'])) {
                $table->index('stock');
            }
            // Index for category filtering
            if (!Schema::hasIndex('products', ['category_id'])) {
                $table->index('category_id');
            }
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            // Index for transaction lookups
            if (!Schema::hasIndex('transaction_items', ['transaction_id'])) {
                $table->index('transaction_id');
            }
            if (!Schema::hasIndex('transaction_items', ['product_id'])) {
                $table->index('product_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['created_at', 'status']);
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['customer_type', 'created_at']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['stock']);
            $table->dropIndex(['category_id']);
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropIndex(['transaction_id']);
            $table->dropIndex(['product_id']);
        });
    }
};