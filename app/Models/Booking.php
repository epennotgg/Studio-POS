<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $guarded = [];

    protected $casts = [
        'booking_date' => 'datetime',
        'package_price' => 'decimal:2',
        'down_payment' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    // Helper method untuk generate booking code
    public static function generateBookingCode()
    {
        $date = date('Ymd');
        $lastBooking = self::where('booking_code', 'like', "BOOK-{$date}-%")->latest()->first();
        
        if ($lastBooking) {
            $lastNumber = intval(substr($lastBooking->booking_code, -4));
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }
        
        return "BOOK-{$date}-{$nextNumber}";
    }

    // Helper method untuk mendapatkan label kategori studio
    public function getStudioCategoryLabelAttribute()
    {
        $labels = [
            'family_graduation' => 'Family/Graduation',
            'prewedding_indoor' => 'Prewedding Indoor',
            'studio_outdoor' => 'Studio Outdoor',
            'sewa_event' => 'Sewa Event',
            'custom' => 'Custom',
        ];
        
        return $labels[$this->studio_category] ?? $this->studio_category;
    }

    // Helper method untuk mendapatkan label status
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
        
        return $labels[$this->status] ?? $this->status;
    }

    // Helper method untuk mendapatkan label metode pembayaran
    public function getPaymentMethodLabelAttribute()
    {
        $labels = [
            'Cash' => 'Tunai (Cash)',
            'Transfer' => 'Transfer Bank',
            'QRIS' => 'QRIS',
        ];
        
        return $labels[$this->payment_method] ?? $this->payment_method;
    }

    // Helper method untuk menghitung additional charge jika melebihi max orang
    public function calculateAdditionalCharge($packageType, $numberOfPeople)
    {
        $additionalCharge = 0;
        
        if ($packageType === 'Paket 3 750k (max 15 orang)' && $numberOfPeople > 15) {
            $additionalPeople = $numberOfPeople - 15;
            $additionalCharge = $additionalPeople * 50000; // 50k per additional person
        }
        
        return $additionalCharge;
    }
}
