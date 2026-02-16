<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    // 1. Halaman Utama Kasir (Menampilkan Produk)
    public function index()
    {
        // Cache produk yang tersedia untuk 5 menit (300 detik)
        // Cache key unik berdasarkan stock status
        $products = cache()->remember('products.available', 300, function () {
            return Product::where('stock', '>', 0)->get();
        });
        
        // Get 15 most popular products based on transaction history
        $popularProducts = cache()->remember('products.popular', 300, function () {
            return Product::where('stock', '>', 0)
                ->withCount(['transactionItems as total_sold' => function($query) {
                    $query->select(DB::raw('SUM(quantity)'));
                }])
                ->orderBy('total_sold', 'desc')
                ->take(15)
                ->get();
        });
        
        return view('transaction.index', compact('products', 'popularProducts'));
    }

    // 2. Proses Checkout (Inti Backend Kasir)
    public function store(Request $request)
    {
        // Validasi data yang dikirim dari frontend
        $request->validate([
            'customer_type' => 'required|in:umum,agen1,agen2',
            'payment_method' => 'required|string',
            'cart' => 'required|array', // Array berisi id produk dan qty
            'cart.*.id' => 'required|exists:products,id',
            'cart.*.qty' => 'required|integer|min:1',
        ]);

        try {
            // DB::transaction memastikan semua proses sukses. 
            // Jika stok gagal dikurangi, transaksi batal otomatis (Rollback).
            $transaction = DB::transaction(function () use ($request) {
                
                // A. Buat Header Transaksi
                $trx = Transaction::create([
                    'invoice_id' => 'INV-' . date('YmdHis') . '-' . rand(100, 999),
                    'user_id' => Auth::id() ?? 1, // Fallback ke ID 1 jika testing tanpa login
                    'customer_name' => $request->customer_name ?? 'Pelanggan Umum',
                    'customer_type' => $request->customer_type,
                    'payment_method' => $request->payment_method,
                    'total_amount' => 0, // Nanti diupdate setelah hitung item
                    'status' => 'paid',
                ]);

                $grandTotal = 0;

                // B. Proses Setiap Item di Keranjang
                foreach ($request->cart as $itemData) {
                    // LockForUpdate mencegah race condition (rebutan stok antar kasir)
                    $product = Product::lockForUpdate()->find($itemData['id']);

                    // Cek ketersediaan stok
                    if ($product->stock < $itemData['qty']) {
                        throw new \Exception("Stok barang '{$product->name}' tidak mencukupi. Sisa: {$product->stock}");
                    }

                    // Ambil harga dinamis dari Model Product sesuai tipe customer
                    $price = $product->getPriceForCustomer($request->customer_type);
                    $subtotal = $price * $itemData['qty'];

                    // Simpan Detail Item
                    TransactionItem::create([
                        'transaction_id' => $trx->id,
                        'product_id' => $product->id,
                        'quantity' => $itemData['qty'],
                        'price_at_transaction' => $price, // Harga dikunci saat transaksi terjadi
                        'subtotal' => $subtotal,
                    ]);

                    // Kurangi Stok Fisik
                    $product->decrement('stock', $itemData['qty']);

                    $grandTotal += $subtotal;
                }

                // C. Update Total Akhir
                $trx->update(['total_amount' => $grandTotal]);

                return $trx;
            });

            // Clear product cache after transaction (stock changed)
            cache()->forget('products.available');

            // Redirect ke halaman struk dalam iframe
            return redirect()->route('transaction.receipt.iframe', $transaction->id);

        } catch (\Exception $e) {
            // Jika error (misal stok habis), kembali ke kasir dengan pesan
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // 3. Menampilkan Struk (Menggunakan view receipt.blade.php Anda)
    public function receipt(Transaction $transaction)
    {
        // Load relasi user dan items->product agar struk lengkap
        $transaction->load(['user', 'items.product']);
        return view('transaction.receipt', compact('transaction'));
    }
    
    // 4. Menampilkan Struk dalam Iframe
    public function receiptIframe(Transaction $transaction)
    {
        // Load relasi user dan items->product agar struk lengkap
        $transaction->load(['user', 'items.product']);
        return view('transaction.receipt_iframe', compact('transaction'));
    }

    // 4. Menampilkan Riwayat Transaksi
    public function history(Request $request)
    {
        $user = Auth::user();
        
        // Jika admin, tampilkan semua transaksi
        // Jika employee, tampilkan hanya transaksi miliknya
        $query = Transaction::with('user');
        
        if ($user->role === 'employee') {
            $query->where('user_id', $user->id);
        }
        
        // Set default 1-week filter jika tidak ada tanggal yang dipilih
        if (!$request->has('start_date') && !$request->has('end_date')) {
            $endDate = date('Y-m-d');
            $startDate = date('Y-m-d', strtotime('-1 week'));
            
            // Set default dates in request untuk ditampilkan di form
            $request->merge([
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            
            $query->whereBetween('created_at', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59'
            ]);
        } else {
            // Filter berdasarkan tanggal yang dipilih user
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('created_at', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59'
                ]);
            } elseif ($request->has('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            } elseif ($request->has('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }
        }
        
        // Filter berdasarkan status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        // Filter berdasarkan tipe pelanggan
        if ($request->has('customer_type') && $request->customer_type !== 'all') {
            $query->where('customer_type', $request->customer_type);
        }
        
        $transactions = $query->latest()->paginate(20);
        
        // Untuk perhitungan revenue, kita perlu menghitung total dari transaksi yang tidak dibatalkan
        $revenueQuery = clone $query;
        $totalRevenue = $revenueQuery->where('status', '!=', 'cancelled')->sum('total_amount');
        $averageTransaction = $revenueQuery->where('status', '!=', 'cancelled')->avg('total_amount');
        
        // Kirim data tambahan ke view
        return view('transaction.history', compact('transactions', 'totalRevenue', 'averageTransaction'));
    }
    
    // 5. Export Riwayat Transaksi ke Excel
    public function export(Request $request)
    {
        $user = Auth::user();
        
        // Jika admin, tampilkan semua transaksi
        // Jika employee, tampilkan hanya transaksi miliknya
        $query = Transaction::with('user');
        
        if ($user->role === 'employee') {
            $query->where('user_id', $user->id);
        }
        
        // Set default 1-week filter jika tidak ada tanggal yang dipilih
        if (!$request->has('start_date') && !$request->has('end_date')) {
            $endDate = date('Y-m-d');
            $startDate = date('Y-m-d', strtotime('-1 week'));
            
            $query->whereBetween('created_at', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59'
            ]);
        } else {
            // Filter berdasarkan tanggal yang dipilih user
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('created_at', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59'
                ]);
            } elseif ($request->has('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            } elseif ($request->has('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }
        }
        
        // Filter berdasarkan status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        // Filter berdasarkan tipe pelanggan
        if ($request->has('customer_type') && $request->customer_type !== 'all') {
            $query->where('customer_type', $request->customer_type);
        }
        
        $transactions = $query->latest()->get();
        
        // Generate CSV content
        $csv = "Invoice ID,Tanggal,Kasir,Pelanggan,Tipe Pelanggan,Metode Bayar,Total,Status\n";
        
        foreach ($transactions as $transaction) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $transaction->invoice_id,
                $transaction->created_at->format('d/m/Y H:i'),
                $transaction->user->name,
                $transaction->customer_name ?? 'Pelanggan Umum',
                $transaction->customer_type,
                $transaction->payment_method,
                number_format($transaction->total_amount, 0, ',', '.'),
                $transaction->status === 'paid' ? 'Lunas' : ($transaction->status === 'pending' ? 'Pending' : 'Dibatalkan')
            );
        }
        
        $filename = 'transaksi_' . date('Ymd_His') . '.csv';
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    // 6. Batalkan Transaksi
    public function cancel(Request $request, Transaction $transaction)
    {
        // Hanya admin yang bisa membatalkan transaksi
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya admin yang dapat membatalkan transaksi.'
            ], 403);
        }

        // Hanya transaksi dengan status 'paid' yang bisa dibatalkan
        if ($transaction->status !== 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya transaksi dengan status LUNAS yang dapat dibatalkan.'
            ], 400);
        }

        try {
            DB::transaction(function () use ($transaction) {
                // Update status transaksi menjadi 'cancelled'
                $transaction->update(['status' => 'cancelled']);
                
                // Kembalikan stok produk yang dibeli
                foreach ($transaction->items as $item) {
                    if ($item->product) {
                        $item->product->increment('stock', $item->quantity);
                    }
                }
            });

            // Clear product cache after stock update
            cache()->forget('products.available');

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibatalkan dan stok produk telah dikembalikan.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan transaksi: ' . $e->getMessage()
            ], 500);
        }
    }
}
