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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique(); // Format: BOOK-YYYYMMDD-XXXX
            $table->foreignId('user_id')->constrained(); // Admin/employee yang membuat booking
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->enum('studio_category', ['family_graduation', 'prewedding_indoor', 'studio_outdoor', 'sewa_event', 'custom']);
            $table->string('package_type');
            $table->decimal('package_price', 12, 2);
            $table->dateTime('booking_date');
            $table->integer('number_of_people');
            $table->decimal('down_payment', 12, 2);
            $table->decimal('total_amount', 12, 2);
            $table->decimal('remaining_amount', 12, 2);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('transaction_id')->nullable()->constrained(); // Jika sudah menjadi transaksi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
