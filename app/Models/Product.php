<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

    // Helper untuk mendapatkan harga berdasarkan tipe customer
    public function getPriceForCustomer($type)
    {
        return match ($type) {
            'agen1' => $this->price_agent1,
            'agen2' => $this->price_agent2,
            default => $this->price_general,
        };
    }

    public function category() { return $this->belongsTo(Category::class); }
    
    public function transactionItems() { return $this->hasMany(TransactionItem::class); }
}