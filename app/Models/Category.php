<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [];
    
    /**
     * Check if this category is a service category (unlimited stock)
     */
    public function isServiceCategory()
    {
        return in_array($this->name, [
            'Jasa cetak',
            'Jasa foto, edit, dan pemasangan'
        ]);
    }
}
