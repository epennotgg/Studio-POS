<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guarded = [];

    public function user() { return $this->belongsTo(User::class); }
    
    public function items() { return $this->hasMany(TransactionItem::class); }

    // Helper method untuk generate invoice ID
    public static function generateInvoiceId()
    {
        $date = date('Ymd');
        $lastTransaction = self::where('invoice_id', 'like', "INV-{$date}-%")->latest()->first();
        
        if ($lastTransaction) {
            $lastNumber = intval(substr($lastTransaction->invoice_id, -4));
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }
        
        return "INV-{$date}-{$nextNumber}";
    }
}
