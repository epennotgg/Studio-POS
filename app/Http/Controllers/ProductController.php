<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    // Menampilkan daftar produk dengan filter dan sorting
    public function index(Request $request)
    {
        $query = Product::with('category');
        
        // Filter berdasarkan kategori
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        
        // Filter berdasarkan stok
        if ($request->has('stock_range') && $request->stock_range) {
            switch ($request->stock_range) {
                case 'low':
                    $query->where('stock', '<', 20);
                    break;
                case 'medium':
                    $query->whereBetween('stock', [20, 100]);
                    break;
                case 'high':
                    $query->where('stock', '>', 100);
                    break;
            }
        }
        
        // Filter berdasarkan pencarian
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('type_color', 'like', "%{$searchTerm}%")
                  ->orWhere('barcode', 'like', "%{$searchTerm}%");
            });
        }
        
        // Sorting
        if ($request->has('sort') && $request->sort) {
            switch ($request->sort) {
                case 'name_asc':
                    $query->orderBy('name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('name', 'desc');
                    break;
                case 'buy_price_asc':
                    $query->orderBy('buy_price', 'asc');
                    break;
                case 'buy_price_desc':
                    $query->orderBy('buy_price', 'desc');
                    break;
                case 'price_general_asc':
                    $query->orderBy('price_general', 'asc');
                    break;
                case 'price_general_desc':
                    $query->orderBy('price_general', 'desc');
                    break;
                case 'price_agent1_asc':
                    $query->orderBy('price_agent1', 'asc');
                    break;
                case 'price_agent1_desc':
                    $query->orderBy('price_agent1', 'desc');
                    break;
                case 'price_agent2_asc':
                    $query->orderBy('price_agent2', 'asc');
                    break;
                case 'price_agent2_desc':
                    $query->orderBy('price_agent2', 'desc');
                    break;
                default:
                    $query->latest();
                    break;
            }
        } else {
            $query->latest();
        }
        
        $products = $query->paginate(20);
        $categories = Category::all();
        
        return view('products.index', compact('products', 'categories'));
    }

    // Menampilkan form tambah produk
    public function create()
    {
        $categories = Category::all();
        return view('products.create', compact('categories'));
    }

    // Menyimpan produk baru ke database
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'category_id'   => 'required|exists:categories,id',
            'type_color'    => 'nullable|string|max:100',
            'barcode'       => 'nullable|string|unique:products,barcode',
            'stock'         => 'required|integer|min:0',
            'buy_price'     => 'required|numeric|min:0',
            'price_general' => 'required|numeric|min:0',
            'price_agent1'  => 'required|numeric|min:0',
            'price_agent2'  => 'required|numeric|min:0',
        ]);

        Product::create($request->all());

        // Clear product cache after creation
        cache()->forget('products.available');

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    // Menampilkan form edit produk
    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('products.edit', compact('product', 'categories'));
    }

    // Mengupdate data produk
    public function update(Request $request, Product $product)
    {
        $validationRules = [
            'name'          => 'required|string|max:255',
            'category_id'   => 'required|exists:categories,id',
            'type_color'    => 'nullable|string|max:100',
            // Ignore ID saat cek unique barcode agar tidak error saat update diri sendiri
            'barcode'       => 'nullable|string|unique:products,barcode,' . $product->id,
            'stock_addition' => 'nullable|integer|min:0',
            'stock_reduction' => 'nullable|integer|min:0|max:' . $product->stock,
            'price_general' => 'required|numeric|min:0',
            'price_agent1'  => 'required|numeric|min:0',
            'price_agent2'  => 'required|numeric|min:0',
        ];
        
        // Hanya admin yang bisa mengubah harga beli
        if (Auth::check() && Auth::user()->role === 'admin') {
            $validationRules['buy_price'] = 'required|numeric|min:0';
        }
        
        $request->validate($validationRules);
        
        // Check if product is a service product (unlimited stock)
        $isServiceProduct = $product->isServiceProduct();
        
        // Hitung stok baru berdasarkan tambahan dan pengurangan
        $currentStock = $product->stock;
        $stockAddition = $request->stock_addition ?? 0;
        $stockReduction = $request->stock_reduction ?? 0;
        
        // Untuk produk jasa, tidak ada pengurangan stok
        if ($isServiceProduct) {
            $stockReduction = 0; // Tidak boleh mengurangi stok untuk produk jasa
        }
        
        // Validasi: pengurangan tidak boleh melebihi stok saat ini (kecuali produk jasa)
        if (!$isServiceProduct && $stockReduction > $currentStock) {
            return back()->withErrors(['stock_reduction' => 'Pengurangan stok tidak boleh melebihi stok saat ini (' . $currentStock . ')']);
        }
        
        // Hitung stok akhir
        $newStock = $currentStock + $stockAddition - $stockReduction;
        
        // Siapkan data untuk update
        $updateData = [
            'name' => $request->name,
            'category_id' => $request->category_id,
            'type_color' => $request->type_color,
            'barcode' => $request->barcode,
            'stock' => $newStock,
            'price_general' => $request->price_general,
            'price_agent1' => $request->price_agent1,
            'price_agent2' => $request->price_agent2,
        ];
        
        // Hanya admin yang bisa mengubah harga beli
        if (Auth::check() && Auth::user()->role === 'admin') {
            $updateData['buy_price'] = $request->buy_price;
        }
        
        $product->update($updateData);

        // Clear product cache after update
        cache()->forget('products.available');

        return redirect()->route('products.index')
            ->with('success', 'Perubahan disimpan! Stok berhasil diupdate: ' . 
                   ($stockAddition > 0 ? '+'.$stockAddition.' tambahan, ' : '') .
                   ($stockReduction > 0 ? '-'.$stockReduction.' pengurangan, ' : '') .
                   'Stok akhir: ' . $newStock);
    }

    // Menghapus produk
    public function destroy(Product $product)
    {
        $product->delete();

        // Clear product cache after deletion
        cache()->forget('products.available');

        return redirect()->route('products.index')
            ->with('success', 'Barang telah dihapus!');
    }
}