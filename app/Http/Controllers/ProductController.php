<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Menampilkan daftar produk
    public function index()
    {
        // Mengambil produk beserta kategori, diurutkan dari yang terbaru
        $products = Product::with('category')->latest()->get();
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
        $request->validate([
            'name'          => 'required|string|max:255',
            'category_id'   => 'required|exists:categories,id',
            'type_color'    => 'nullable|string|max:100',
            // Ignore ID saat cek unique barcode agar tidak error saat update diri sendiri
            'barcode'       => 'nullable|string|unique:products,barcode,' . $product->id,
            'stock'         => 'required|integer|min:0',
            'buy_price'     => 'required|numeric|min:0',
            'price_general' => 'required|numeric|min:0',
            'price_agent1'  => 'required|numeric|min:0',
            'price_agent2'  => 'required|numeric|min:0',
        ]);

        $product->update($request->all());

        // Clear product cache after update
        cache()->forget('products.available');

        return redirect()->route('products.index')
            ->with('success', 'Perubahan disimpan!');
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