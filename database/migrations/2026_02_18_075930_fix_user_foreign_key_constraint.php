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
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        // Re-add the foreign key with cascade on delete
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the cascade foreign key
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        // Re-add the original foreign key without cascade
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users');
        });
    }
};