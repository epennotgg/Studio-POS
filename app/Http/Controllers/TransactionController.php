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
            'down_payment' => 'nullable|numeric|min:0',
            'is_dp' => 'nullable|boolean',
        ]);

        try {
            // DB::transaction memastikan semua proses sukses. 
            // Jika stok gagal dikurangi, transaksi batal otomatis (Rollback).
            $transaction = DB::transaction(function () use ($request) {
                
                // Tentukan status transaksi berdasarkan DP
                $isDP = $request->has('is_dp') && $request->is_dp == '1';
                $downPayment = $isDP ? ($request->down_payment ?? 0) : 0;
                $status = $isDP ? 'pending' : 'paid';
                
                // A. Buat Header Transaksi
                $trx = Transaction::create([
                    'invoice_id' => 'INV-' . date('YmdHis') . '-' . rand(100, 999),
                    'user_id' => Auth::id() ?? 1, // Fallback ke ID 1 jika testing tanpa login
                    'customer_name' => $request->customer_name ?? 'Pelanggan Umum',
                    'customer_phone' => $request->customer_phone ?? null,
                    'customer_type' => $request->customer_type,
                    'payment_method' => $request->payment_method,
                    'total_amount' => 0, // Nanti diupdate setelah hitung item
                    'down_payment' => $downPayment,
                    'remaining_amount' => 0, // Nanti diupdate setelah hitung item
                    'status' => $status,
                ]);

                $grandTotal = 0;

                // B. Proses Setiap Item di Keranjang
                foreach ($request->cart as $itemData) {
                    // LockForUpdate mencegah race condition (rebutan stok antar kasir)
                    $product = Product::lockForUpdate()->find($itemData['id']);

                    // Cek ketersediaan stok (kecuali untuk produk jasa)
                    if (!$product->isServiceProduct() && $product->stock < $itemData['qty']) {
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

                    // Kurangi Stok Fisik (kecuali untuk produk jasa)
                    if (!$product->isServiceProduct()) {
                        $product->decrement('stock', $itemData['qty']);
                    }

                    $grandTotal += $subtotal;
                }

                // C. Update Total Akhir dan sisa pembayaran
                $remainingAmount = $grandTotal - $downPayment;
                $trx->update([
                    'total_amount' => $grandTotal,
                    'remaining_amount' => $remainingAmount,
                ]);

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
        // Jika status "pending", input DP sebagai pemasukan
        // Jika status "lunas", input total harga sebagai pemasukan
        $revenueQuery = clone $query;
        $totalRevenue = $revenueQuery->where('status', '!=', 'cancelled')
            ->get()
            ->sum(function($transaction) {
                if ($transaction->status === 'pending') {
                    return $transaction->down_payment;
                } else {
                    return $transaction->total_amount;
                }
            });
        
        // Hitung rata-rata transaksi
        $validTransactions = $revenueQuery->where('status', '!=', 'cancelled')->get();
        $averageTransaction = $validTransactions->count() > 0 
            ? $validTransactions->avg(function($transaction) {
                if ($transaction->status === 'pending') {
                    return $transaction->down_payment;
                } else {
                    return $transaction->total_amount;
                }
            })
            : 0;
        
        // Hitung jumlah transaksi pending
        $pendingTransactionsCount = $query->clone()->where('status', 'pending')->count();
        
        // ==================== KALKULASI OMSEET ====================
        
        // 1. Omset 7 Hari Terakhir (hari ini sampai 7 hari sebelumnya)
        $weeklyEndDate = date('Y-m-d');
        $weeklyStartDate = date('Y-m-d', strtotime('-7 days'));
        
        $weeklyQuery = Transaction::query();
        if ($user->role === 'employee') {
            $weeklyQuery->where('user_id', $user->id);
        }
        
        $weeklyTransactions = $weeklyQuery->whereBetween('created_at', [
            $weeklyStartDate . ' 00:00:00',
            $weeklyEndDate . ' 23:59:59'
        ])->where('status', '!=', 'cancelled')->get();
        
        $weeklyRevenue = $weeklyTransactions->sum(function($transaction) {
            if ($transaction->status === 'pending') {
                return $transaction->down_payment;
            } else {
                return $transaction->total_amount;
            }
        });
        
        $weeklyTransactionCount = $weeklyTransactions->count();
        $weeklyAverage = $weeklyTransactionCount > 0 ? $weeklyRevenue / $weeklyTransactionCount : 0;
        
        // 2. Omset Bulan Ini (tanggal 1 sampai hari ini)
        $monthlyStartDate = date('Y-m-01'); // Tanggal 1 bulan ini
        $monthlyEndDate = date('Y-m-d'); // Hari ini
        
        $monthlyQuery = Transaction::query();
        if ($user->role === 'employee') {
            $monthlyQuery->where('user_id', $user->id);
        }
        
        $monthlyTransactions = $monthlyQuery->whereBetween('created_at', [
            $monthlyStartDate . ' 00:00:00',
            $monthlyEndDate . ' 23:59:59'
        ])->where('status', '!=', 'cancelled')->get();
        
        $monthlyRevenue = $monthlyTransactions->sum(function($transaction) {
            if ($transaction->status === 'pending') {
                return $transaction->down_payment;
            } else {
                return $transaction->total_amount;
            }
        });
        
        $monthlyTransactionCount = $monthlyTransactions->count();
        $monthlyAverage = $monthlyTransactionCount > 0 ? $monthlyRevenue / $monthlyTransactionCount : 0;
        
        // 3. Omset 30 Hari Terakhir (hari ini sampai 30 hari kebelakang)
        $last30DaysEndDate = date('Y-m-d');
        $last30DaysStartDate = date('Y-m-d', strtotime('-30 days'));
        
        $last30DaysQuery = Transaction::query();
        if ($user->role === 'employee') {
            $last30DaysQuery->where('user_id', $user->id);
        }
        
        $last30DaysTransactions = $last30DaysQuery->whereBetween('created_at', [
            $last30DaysStartDate . ' 00:00:00',
            $last30DaysEndDate . ' 23:59:59'
        ])->where('status', '!=', 'cancelled')->get();
        
        $last30DaysRevenue = $last30DaysTransactions->sum(function($transaction) {
            if ($transaction->status === 'pending') {
                return $transaction->down_payment;
            } else {
                return $transaction->total_amount;
            }
        });
        
        $last30DaysTransactionCount = $last30DaysTransactions->count();
        $last30DaysAverage = $last30DaysTransactionCount > 0 ? $last30DaysRevenue / $last30DaysTransactionCount : 0;
        
        // ==================== BREAKDOWN REVENUE ====================
        
        // Breakdown berdasarkan status
        $paidRevenue = $query->clone()->where('status', 'paid')->sum('total_amount');
        $pendingRevenue = $query->clone()->where('status', 'pending')->sum('down_payment');
        $downPaymentRevenue = $query->clone()->where('status', 'pending')->sum('down_payment');
        
        // Calculate DP profit percentage (current month vs last month)
        $currentMonth = date('Y-m');
        $lastMonth = date('Y-m', strtotime('-1 month'));
        
        // Get current month down payments
        $currentMonthDPQuery = Transaction::query();
        if ($user->role === 'employee') {
            $currentMonthDPQuery->where('user_id', $user->id);
        }
        
        $currentMonthDP = $currentMonthDPQuery->where('status', 'pending')
            ->whereYear('created_at', date('Y'))
            ->whereMonth('created_at', date('m'))
            ->sum('down_payment');
        
        // Get last month down payments
        $lastMonthDPQuery = Transaction::query();
        if ($user->role === 'employee') {
            $lastMonthDPQuery->where('user_id', $user->id);
        }
        
        $lastMonthDP = $lastMonthDPQuery->where('status', 'pending')
            ->whereYear('created_at', date('Y', strtotime('-1 month')))
            ->whereMonth('created_at', date('m', strtotime('-1 month')))
            ->sum('down_payment');
        
        // Calculate profit percentage
        $dpProfitPercentage = 0;
        if ($lastMonthDP > 0) {
            $dpProfitPercentage = (($currentMonthDP - $lastMonthDP) / $lastMonthDP) * 100;
        } elseif ($currentMonthDP > 0 && $lastMonthDP == 0) {
            // If last month was 0 and current month has DP, show 100% growth
            $dpProfitPercentage = 100;
        }
        
        // Breakdown berdasarkan tipe pelanggan
        $generalRevenue = $query->clone()->where('customer_type', 'umum')
            ->where('status', '!=', 'cancelled')
            ->get()
            ->sum(function($transaction) {
                if ($transaction->status === 'pending') {
                    return $transaction->down_payment;
                } else {
                    return $transaction->total_amount;
                }
            });
        
        $agent1Revenue = $query->clone()->where('customer_type', 'agen1')
            ->where('status', '!=', 'cancelled')
            ->get()
            ->sum(function($transaction) {
                if ($transaction->status === 'pending') {
                    return $transaction->down_payment;
                } else {
                    return $transaction->total_amount;
                }
            });
        
        $agent2Revenue = $query->clone()->where('customer_type', 'agen2')
            ->where('status', '!=', 'cancelled')
            ->get()
            ->sum(function($transaction) {
                if ($transaction->status === 'pending') {
                    return $transaction->down_payment;
                } else {
                    return $transaction->total_amount;
                }
            });
        
        // Breakdown berdasarkan metode pembayaran
        $cashRevenue = $query->clone()->where('payment_method', 'Cash')
            ->where('status', '!=', 'cancelled')
            ->get()
            ->sum(function($transaction) {
                if ($transaction->status === 'pending') {
                    return $transaction->down_payment;
                } else {
                    return $transaction->total_amount;
                }
            });
        
        $transferRevenue = $query->clone()->where('payment_method', 'Transfer')
            ->where('status', '!=', 'cancelled')
            ->get()
            ->sum(function($transaction) {
                if ($transaction->status === 'pending') {
                    return $transaction->down_payment;
                } else {
                    return $transaction->total_amount;
                }
            });
        
        $qrisRevenue = $query->clone()->where('payment_method', 'QRIS')
            ->where('status', '!=', 'cancelled')
            ->get()
            ->sum(function($transaction) {
                if ($transaction->status === 'pending') {
                    return $transaction->down_payment;
                } else {
                    return $transaction->total_amount;
                }
            });
        
        // Format tanggal untuk tampilan
        $weeklyStartDateFormatted = date('d/m/Y', strtotime($weeklyStartDate));
        $weeklyEndDateFormatted = date('d/m/Y', strtotime($weeklyEndDate));
        $monthlyPeriod = date('d/m/Y', strtotime($monthlyStartDate)) . ' - ' . date('d/m/Y', strtotime($monthlyEndDate));
        $last30DaysStartDateFormatted = date('d/m/Y', strtotime($last30DaysStartDate));
        $last30DaysEndDateFormatted = date('d/m/Y', strtotime($last30DaysEndDate));
        
        // Kirim data tambahan ke view
        return view('transaction.history', compact(
            'transactions', 
            'totalRevenue', 
            'averageTransaction',
            'pendingTransactionsCount',
            // Omset data
            'weeklyRevenue',
            'weeklyTransactionCount',
            'weeklyAverage',
            'weeklyStartDate',
            'weeklyEndDate',
            'weeklyStartDateFormatted',
            'weeklyEndDateFormatted',
            // Monthly data
            'monthlyRevenue',
            'monthlyTransactionCount',
            'monthlyAverage',
            'monthlyPeriod',
            // Last 30 days data
            'last30DaysRevenue',
            'last30DaysTransactionCount',
            'last30DaysAverage',
            'last30DaysStartDateFormatted',
            'last30DaysEndDateFormatted',
            // Breakdown data
            'paidRevenue',
            'pendingRevenue',
            'downPaymentRevenue',
            'dpProfitPercentage',
            'generalRevenue',
            'agent1Revenue',
            'agent2Revenue',
            'cashRevenue',
            'transferRevenue',
            'qrisRevenue'
        ));
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
        $user = Auth::user();
        
        // Admin bisa membatalkan semua transaksi
        // Kasir hanya bisa membatalkan transaksi yang mereka layani sendiri
        if ($user->role !== 'admin' && $transaction->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya dapat membatalkan transaksi yang Anda layani sendiri.'
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
                
                // Kembalikan stok produk yang dibeli (kecuali untuk produk jasa)
                foreach ($transaction->items as $item) {
                    if ($item->product && !$item->product->isServiceProduct()) {
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

    // 7. Tandai Transaksi sebagai Lunas
    public function markAsPaid(Request $request, Transaction $transaction)
    {
        $user = Auth::user();
        
        // Admin bisa menandai semua transaksi sebagai lunas
        // Kasir hanya bisa menandai transaksi yang mereka layani sendiri
        if ($user->role !== 'admin' && $transaction->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya dapat menandai transaksi yang Anda layani sendiri sebagai lunas.'
            ], 403);
        }

        // Hanya transaksi dengan status 'pending' yang bisa ditandai sebagai lunas
        if ($transaction->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya transaksi dengan status PENDING yang dapat ditandai sebagai lunas.'
            ], 400);
        }

        try {
            DB::transaction(function () use ($transaction) {
                // Update status transaksi menjadi 'paid' dan sisa pembayaran menjadi 0
                $transaction->update([
                    'status' => 'paid',
                    'remaining_amount' => 0,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil ditandai sebagai LUNAS!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai transaksi sebagai lunas: ' . $e->getMessage()
            ], 500);
        }
    }
}
