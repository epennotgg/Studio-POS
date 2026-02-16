<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    // Dashboard untuk Employee
    public function employeeDashboard()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // Jumlah order hari ini (hanya transaksi user ini)
        $todayOrders = Transaction::where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->count();

        // Jumlah order minggu ini (hanya transaksi user ini)
        $weekOrders = Transaction::where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->count();

        // Omset minggu ini (hanya transaksi user ini)
        $weekRevenue = Transaction::where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->where('status', 'paid')
            ->sum('total_amount');

        // Produk dengan stok rendah (kurang dari 10)
        $lowStockProducts = Product::where('stock', '<', 10)
            ->orderBy('stock', 'asc')
            ->limit(5)
            ->get();

        // Transaksi terbaru (hanya user ini)
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->with('user')
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard.employee', compact(
            'todayOrders',
            'weekOrders',
            'weekRevenue',
            'lowStockProducts',
            'recentTransactions'
        ));
    }

    // Dashboard untuk Admin
    public function adminDashboard()
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // Jumlah order hari ini (semua transaksi)
        $todayOrders = Transaction::whereDate('created_at', $today)->count();

        // Jumlah order minggu ini (semua transaksi)
        $weekOrders = Transaction::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();

        // Omset minggu ini (semua transaksi)
        $weekRevenue = Transaction::whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->where('status', 'paid')
            ->sum('total_amount');

        // Statistik karyawan (performance)
        $employeePerformance = User::where('role', 'employee')
            ->withCount(['transactions as today_transactions' => function ($query) use ($today) {
                $query->whereDate('created_at', $today);
            }])
            ->withCount(['transactions as week_transactions' => function ($query) use ($startOfWeek, $endOfWeek) {
                $query->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
            }])
            ->withSum(['transactions as week_revenue' => function ($query) use ($startOfWeek, $endOfWeek) {
                $query->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->where('status', 'paid');
            }], 'total_amount')
            ->get();

        // Produk dengan stok rendah
        $lowStockProducts = Product::where('stock', '<', 10)
            ->orderBy('stock', 'asc')
            ->limit(10)
            ->get();

        // Transaksi terbaru (semua)
        $recentTransactions = Transaction::with('user')
            ->latest()
            ->limit(10)
            ->get();

        // Statistik penjualan per kategori
        $categorySales = DB::table('transaction_items')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'categories.name as category_name',
                DB::raw('SUM(transaction_items.quantity) as total_quantity'),
                DB::raw('SUM(transaction_items.subtotal) as total_revenue')
            )
            ->whereBetween('transaction_items.created_at', [$startOfWeek, $endOfWeek])
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_revenue', 'desc')
            ->get();

        return view('dashboard.admin', compact(
            'todayOrders',
            'weekOrders',
            'weekRevenue',
            'employeePerformance',
            'lowStockProducts',
            'recentTransactions',
            'categorySales'
        ));
    }
}