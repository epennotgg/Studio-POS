<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing categories to new names
        DB::table('categories')->where('name', 'Jasa Foto dan Cetak')->update(['name' => 'Jasa cetak']);
        DB::table('categories')->where('name', 'Bingkai & Album')->update(['name' => 'Bingkai']);
        DB::table('categories')->where('name', 'Aksesoris & Perlengkapan')->update(['name' => 'Album']);
        
        // Add new categories if they don't exist
        $newCategories = [
            'Jasa foto, edit, dan pemasangan',
            'Penyimpanan (FD/SDC)',
            'Bahan cetak',
            'Lainnya'
        ];
        
        foreach ($newCategories as $categoryName) {
            if (!DB::table('categories')->where('name', $categoryName)->exists()) {
                Category::create(['name' => $categoryName]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original categories
        DB::table('categories')->where('name', 'Jasa cetak')->update(['name' => 'Jasa Foto dan Cetak']);
        DB::table('categories')->where('name', 'Bingkai')->update(['name' => 'Bingkai & Album']);
        DB::table('categories')->where('name', 'Album')->update(['name' => 'Aksesoris & Perlengkapan']);
        
        // Remove new categories
        DB::table('categories')->whereIn('name', [
            'Jasa foto, edit, dan pemasangan',
            'Penyimpanan (FD/SDC)',
            'Bahan cetak',
            'Lainnya'
        ])->delete();
    }
};
