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
        // Drop the existing foreign key constraint
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        // Re-add the foreign key with cascade on delete
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the cascade foreign key
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        // Re-add the original foreign key without cascade
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products');
        });
    }
};