@extends('layouts.app')

@section('title', 'Riwayat Transaksi')
@section('page-title', 'Riwayat Transaksi')

@section('header-actions')
    <button type="button" class="btn btn-success" onclick="exportToExcel()">
        <i class="fas fa-file-excel mr-1"></i> Export Excel
    </button>
@endsection

@section('content')
<div class="card mb-6">
    <div class="card-header flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900">Filter Transaksi</h3>
        <button type="button" class="btn btn-secondary" id="toggleFilterBtn" onclick="toggleFilter()">
            <i class="fas fa-chevron-up mr-1"></i> <span id="filterToggleText">Sembunyikan Filter</span>
        </button>
    </div>
    <div class="card-body" id="filterFormContainer">
        <form method="GET" action="{{ route('transaction.history') }}" id="filterForm">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div class="form-group">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Tanggal Akhir</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Tipe Pelanggan</label>
                    <select name="customer_type" class="form-control">
                        <option value="all" {{ request('customer_type') == 'all' ? 'selected' : '' }}>Semua Tipe</option>
                        <option value="umum" {{ request('customer_type') == 'umum' ? 'selected' : '' }}>Umum</option>
                        <option value="agen1" {{ request('customer_type') == 'agen1' ? 'selected' : '' }}>Agen 1</option>
                        <option value="agen2" {{ request('customer_type') == 'agen2' ? 'selected' : '' }}>Agen 2</option>
                    </select>
                </div>
            </div>
            
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search mr-1"></i> Terapkan Filter
                </button>
                <a href="{{ route('transaction.history') }}" class="btn btn-warning">
                    <i class="fas fa-redo mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

@if(Auth::user()->role === 'admin')
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="card">
        <div class="card-body">
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Total Transaksi</h3>
            <div class="mt-2">
                <div class="text-3xl font-bold text-gray-900">{{ $transactions->total() }}</div>
                <p class="mt-1 text-sm text-gray-600">Jumlah transaksi ditemukan</p>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Total Pendapatan</h3>
            <div class="mt-2">
                <div class="text-3xl font-bold text-gray-900">Rp {{ number_format($totalRevenue ?? $transactions->sum('total_amount'), 0, ',', '.') }}</div>
                <p class="mt-1 text-sm text-gray-600">Total revenue dari transaksi</p>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Rata-rata Transaksi</h3>
            <div class="mt-2">
                <div class="text-3xl font-bold text-gray-900">Rp {{ number_format($averageTransaction ?? $transactions->avg('total_amount') ?? 0, 0, ',', '.') }}</div>
                <p class="mt-1 text-sm text-gray-600">Rata-rata nilai transaksi</p>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Transaksi Pending</h3>
            <div class="mt-2">
                <div class="text-3xl font-bold text-yellow-600">{{ $pendingTransactionsCount ?? 0 }}</div>
                <p class="mt-1 text-sm text-gray-600">Menunggu pembayaran lunas</p>
            </div>
        </div>
    </div>
</div>

<!-- Omset Section -->
<div class="card mb-6">
    <div class="card-header">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Statistik Omset</h3>
    </div>
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Omset 7 Hari Terakhir -->
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-lg p-6 border border-blue-200 dark:border-blue-700/30">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-300 uppercase tracking-wider">Omset 7 Hari</h3>
                        <div class="mt-2">
                            <div class="text-3xl font-bold text-blue-900 dark:text-blue-200">Rp {{ number_format($weeklyRevenue ?? 0, 0, ',', '.') }}</div>
                            <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                                {{ $weeklyStartDate ?? 'Hari ini' }} - {{ $weeklyEndDate ?? '7 hari lalu' }}
                            </p>
                        </div>
                    </div>
                    <div class="text-blue-500 dark:text-blue-400">
                        <i class="fas fa-calendar-week text-3xl"></i>
                    </div>
                </div>
                <div class="mt-4 text-sm text-blue-700 dark:text-blue-300">
                    <div class="flex justify-between">
                        <span class="dark:text-blue-200">Transaksi:</span>
                        <span class="font-semibold dark:text-blue-100">{{ $weeklyTransactionCount ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between mt-1">
                        <span class="dark:text-blue-200">Rata-rata:</span>
                        <span class="font-semibold dark:text-blue-100">Rp {{ number_format($weeklyAverage ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Omset Bulan Ini (1-31) -->
            <div class="bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-lg p-6 border border-green-200 dark:border-green-700/30">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-green-800 dark:text-green-300 uppercase tracking-wider">Omset Bulan Ini</h3>
                        <div class="mt-2">
                            <div class="text-3xl font-bold text-green-900 dark:text-green-200">Rp {{ number_format($monthlyRevenue ?? 0, 0, ',', '.') }}</div>
                            <p class="mt-1 text-sm text-green-700 dark:text-green-300">
                                {{ $monthlyPeriod ?? '1' }} - {{ date('d/m/Y') }}
                            </p>
                        </div>
                    </div>
                    <div class="text-green-500 dark:text-green-400">
                        <i class="fas fa-calendar-alt text-3xl"></i>
                    </div>
                </div>
                <div class="mt-4 text-sm text-green-700 dark:text-green-300">
                    <div class="flex justify-between">
                        <span class="dark:text-green-200">Transaksi:</span>
                        <span class="font-semibold dark:text-green-100">{{ $monthlyTransactionCount ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between mt-1">
                        <span class="dark:text-green-200">Rata-rata:</span>
                        <span class="font-semibold dark:text-green-100">Rp {{ number_format($monthlyAverage ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Omset 30 Hari Terakhir -->
            <div class="bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-lg p-6 border border-purple-200 dark:border-purple-700/30">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-purple-800 dark:text-purple-300 uppercase tracking-wider">Omset 30 Hari</h3>
                        <div class="mt-2">
                            <div class="text-3xl font-bold text-purple-900 dark:text-purple-200">Rp {{ number_format($last30DaysRevenue ?? 0, 0, ',', '.') }}</div>
                            <p class="mt-1 text-sm text-purple-700 dark:text-purple-300">
                                {{ $last30DaysStartDateFormatted ?? 'Hari ini' }} - {{ $last30DaysEndDateFormatted ?? '30 hari lalu' }}
                            </p>
                        </div>
                    </div>
                    <div class="text-purple-500 dark:text-purple-400">
                        <i class="fas fa-chart-line text-3xl"></i>
                    </div>
                </div>
                <div class="mt-4 text-sm text-purple-700 dark:text-purple-300">
                    <div class="flex justify-between">
                        <span class="dark:text-purple-200">Transaksi:</span>
                        <span class="font-semibold dark:text-purple-100">{{ $last30DaysTransactionCount ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between mt-1">
                        <span class="dark:text-purple-200">Rata-rata:</span>
                        <span class="font-semibold dark:text-purple-100">Rp {{ number_format($last30DaysAverage ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Revenue Breakdown -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <h4 class="text-sm font-semibold mb-2">Breakdown Status</h4>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Lunas:</span>
                        <span class="font-semibold text-green-600 dark:text-green-400">Rp {{ number_format($paidRevenue ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Uang DP/Pending:</span>
                        <span class="font-semibold text-yellow-600 dark:text-yellow-400">Rp {{ number_format($pendingRevenue ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Keuntungan (vs Bulan Lalu):</span>
                    <span class="font-semibold @if(($dpProfitPercentage ?? 0) >= 0) text-green-600 dark:text-green-400 @else text-red-600 dark:text-red-400 @endif">
                        @if(($dpProfitPercentage ?? 0) >= 0)+@endif{{ number_format($dpProfitPercentage ?? 0, 1) }}%
                    </span>
                </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <h4 class="text-sm font-semibold mb-2">Breakdown Tipe Pelanggan</h4>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Umum:</span>
                        <span class="font-semibold text-blue-600 dark:text-blue-400">Rp {{ number_format($generalRevenue ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Agen 1:</span>
                        <span class="font-semibold text-blue-600 dark:text-blue-400">Rp {{ number_format($agent1Revenue ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Agen 2:</span>
                        <span class="font-semibold text-blue-600 dark:text-blue-400">Rp {{ number_format($agent2Revenue ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <h4 class="text-sm font-semibold mb-2">Breakdown Metode Bayar</h4>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Tunai:</span>
                        <span class="font-semibold text-blue-600 dark:text-blue-400">Rp {{ number_format($cashRevenue ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Transfer:</span>
                        <span class="font-semibold text-blue-600 dark:text-blue-400">Rp {{ number_format($transferRevenue ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">QRIS:</span>
                        <span class="font-semibold text-blue-600 dark:text-blue-400">Rp {{ number_format($qrisRevenue ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900">Daftar Transaksi</h3>
        <div class="text-sm text-gray-600">
            Menampilkan {{ $transactions->firstItem() ?? 0 }}-{{ $transactions->lastItem() ?? 0 }} dari {{ $transactions->total() }} transaksi
        </div>
    </div>
    <div class="card-body">
        @if($transactions->count() > 0)
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Invoice ID</th>
                        <th>Tanggal & Jam</th>
                        @if(Auth::user()->role === 'admin')
                        <th>Kasir</th>
                        @endif
                        <th>Pelanggan</th>
                        <th>Tipe</th>
                        <th>Metode Bayar</th>
                        <th class="text-right">Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                    <tr>
                        <td class="font-medium">{{ $transaction->invoice_id }}</td>
                        <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                        @if(Auth::user()->role === 'admin')
                        <td>
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8 flex items-center justify-center rounded-full bg-blue-100 text-blue-800 font-bold text-sm">
                                    {{ strtoupper(substr($transaction->user->name, 0, 1)) }}
                                </div>
                                <div class="ml-2">
                                    <div class="text-sm font-medium">{{ $transaction->user->name }}</div>
                                </div>
                            </div>
                        </td>
                        @endif
                        <td>
                            <div class="font-medium">{{ $transaction->customer_name ?? 'Pelanggan Umum' }}</div>
                            @if($transaction->customer_phone)
                            <div class="text-sm text-gray-600">{{ $transaction->customer_phone }}</div>
                            @endif
                        </td>
                        <td>
                            @if($transaction->customer_type === 'umum')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-badge text-xs font-medium bg-gray-100 text-gray-800">
                                Umum
                            </span>
                            @elseif($transaction->customer_type === 'agen1')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-badge text-xs font-medium bg-blue-100 text-blue-800">
                                Agen 1
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-badge text-xs font-medium bg-purple-100 text-purple-800">
                                Agen 2
                            </span>
                            @endif
                        </td>
                        <td>
                            @if($transaction->payment_method === 'Cash')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-badge text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-money-bill-wave mr-1"></i> Tunai
                            </span>
                            @elseif($transaction->payment_method === 'Transfer')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-badge text-xs font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-university mr-1"></i> Transfer
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-badge text-xs font-medium bg-yellow-100 text-yellow-800">
                                <i class="fas fa-qrcode mr-1"></i> QRIS
                            </span>
                            @endif
                        </td>
                        <td class="text-right font-medium text-green-600">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                        <td>
                            @if($transaction->status === 'paid')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-badge text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i> Lunas
                            </span>
                            @elseif($transaction->status === 'pending')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-badge text-xs font-medium bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-1"></i> Pending
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-badge text-xs font-medium bg-red-100 text-red-800">
                                <i class="fas fa-times-circle mr-1"></i> Dibatalkan
                            </span>
                            @endif
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <button type="button" class="btn btn-warning text-sm" onclick="viewTransactionDetails({{ $transaction->id }})">
                                    <i class="fas fa-eye mr-1"></i> Detail
                                </button>
                                @if($transaction->status === 'paid' && (Auth::user()->role === 'admin' || Auth::id() == $transaction->user_id))
                                <button type="button" class="btn btn-danger text-sm" onclick="cancelTransaction({{ $transaction->id }})">
                                    <i class="fas fa-times-circle mr-1"></i> Batal
                                </button>
                                @endif
                                @if($transaction->status === 'pending' && (Auth::user()->role === 'admin' || Auth::id() == $transaction->user_id))
                                <button type="button" class="btn btn-success text-sm" onclick="markAsPaid({{ $transaction->id }})">
                                    <i class="fas fa-check-circle mr-1"></i> Lunas
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-6 pagination">
            {{ $transactions->links() }}
        </div>
        @else
        <div class="text-center py-8 text-gray-600">
            <div class="text-4xl mb-2"><i class="fas fa-chart-bar"></i></div>
            <p class="text-lg font-medium mb-2">Tidak ada transaksi yang ditemukan</p>
            <p class="text-sm">Coba ubah filter pencarian atau mulai transaksi baru di kasir.</p>
            <a href="{{ route('transaction.index') }}" class="btn btn-primary mt-4">
                <i class="fas fa-cash-register mr-1"></i> Ke Kasir
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Transaction Details Modal -->
<div class="modal" id="transactionDetailsModal">
    <div class="modal-content" style="max-width: 400px; width: 100%;">
        <div class="modal-header">
            <h3 class="text-lg font-semibold text-gray-900">Detail Transaksi</h3>
            <div class="flex gap-2">
                <button type="button" class="btn btn-primary" onclick="printTransactionDetails()">
                    <i class="fas fa-print mr-1"></i> Cetak
                </button>
                <button type="button" onclick="closeModal('transactionDetailsModal')" class="btn btn-danger">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="modal-body" style="padding: 0;">
            <div id="transactionDetailsContent" style="width: 100%;">
                <!-- Details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal" id="receiptModal">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h3 class="text-lg font-semibold text-gray-900">Struk Transaksi</h3>
            <div class="flex gap-2">
                <button type="button" class="btn btn-primary" onclick="printReceipt()">
                    <i class="fas fa-print mr-1"></i> Cetak
                </button>
                <button type="button" onclick="closeModal('receiptModal')" class="btn btn-danger">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="modal-body">
            <div id="receiptContent">
                <!-- Receipt will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
    function exportToExcel() {
        // Get current filter parameters
        const form = document.getElementById('filterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        
        // Redirect to export endpoint with current filters
        window.location.href = `/transaksi/riwayat/export?${params.toString()}`;
    }
    
    // View receipt in modal
    async function viewReceipt(receiptUrl) {
        try {
            const response = await fetch(receiptUrl);
            const html = await response.text();
            
            document.getElementById('receiptContent').innerHTML = html;
            openModal('receiptModal');
        } catch (error) {
            console.error('Error loading receipt:', error);
            alert('Gagal memuat struk transaksi');
        }
    }
    
    // Print receipt
    function printReceipt() {
        const receiptContent = document.getElementById('receiptContent');
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Cetak Struk</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                    @media print {
                        body { padding: 0; }
                        .no-print { display: none !important; }
                    }
                </style>
            </head>
            <body>
                ${receiptContent.innerHTML}
                <div class="no-print" style="margin-top: 20px; text-align: center;">
                    <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        Cetak
                    </button>
                    <button onclick="window.close()" style="padding: 10px 20px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">
                        Tutup
                    </button>
                </div>
            </body>
            </html>
        `);
        printWindow.document.close();
    }
    
    // Direct print receipt (like CTRL+P)
    function directPrintReceipt(receiptUrl) {
        const printWindow = window.open(receiptUrl, '_blank');
        printWindow.onload = function() {
            printWindow.print();
        };
    }
    
    // Validate date range before form submission
    document.getElementById('filterForm').addEventListener('submit', function(e) {
        const startDateInput = document.querySelector('input[name="start_date"]');
        const endDateInput = document.querySelector('input[name="end_date"]');
        
        if (startDateInput.value && endDateInput.value) {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);
            
            if (startDate > endDate) {
                e.preventDefault();
                alert('Tanggal mulai tidak boleh melewati tanggal akhir!');
                startDateInput.focus();
                return false;
            }
        }
        
        return true;
    });
    
    // Auto-submit form when date inputs change
    document.querySelectorAll('input[type="date"]').forEach(input => {
        input.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
</script>

<script>
let currentTransactionId = null;

function resizeIframe(obj) {
    obj.style.height = obj.contentWindow.document.documentElement.scrollHeight + 100 + 'px';
}

async function viewTransactionDetails(transactionId) {
    try {
        currentTransactionId = transactionId;
        
        // Create an iframe to isolate the receipt content
        const iframeHtml = `
            <iframe 
                src="/transaksi/struk-iframe/${transactionId}" 
                style="width: 100%; border: none; overflow: auto;"
                frameborder="0"
                allowfullscreen
                onload="resizeIframe(this)"
            ></iframe>
        `;
        
        document.getElementById('transactionDetailsContent').innerHTML = iframeHtml;
        openModal('transactionDetailsModal');
    } catch (error) {
        console.error('Error loading transaction details:', error);
        alert('Gagal memuat detail transaksi');
    }
}

// Print transaction details
function printTransactionDetails(transactionId = null) {
    const id = transactionId || currentTransactionId;
    if (!id) {
        alert('Tidak ada transaksi yang dipilih');
        return;
    }
    
    // Open receipt in new window for printing
    const printWindow = window.open(`/transaksi/struk-iframe/${id}`, '_blank');
    printWindow.onload = function() {
        printWindow.print();
    };
}

// Cancel transaction
async function cancelTransaction(transactionId) {
    if (!confirm('Apakah Anda yakin ingin membatalkan transaksi ini? Transaksi yang dibatalkan tidak akan dihitung dalam revenue.')) {
        return;
    }
    
    try {
        const response = await fetch(`/transaksi/${transactionId}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const result = await response.json();
        
        if (response.ok) {
            alert('Transaksi berhasil dibatalkan!');
            location.reload(); // Reload halaman untuk update data
        } else {
            alert('Gagal membatalkan transaksi: ' + (result.message || 'Terjadi kesalahan'));
        }
    } catch (error) {
        console.error('Error cancelling transaction:', error);
        alert('Gagal membatalkan transaksi. Silakan coba lagi.');
    }
}

// Mark transaction as paid
async function markAsPaid(transactionId) {
    if (!confirm('Apakah Anda yakin ingin menandai transaksi ini sebagai LUNAS? Status akan berubah dan invoice akan diganti menjadi lunas.')) {
        return;
    }
    
    try {
        const response = await fetch(`/transaksi/${transactionId}/mark-as-paid`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const result = await response.json();
        
        if (response.ok) {
            alert('Transaksi berhasil ditandai sebagai LUNAS!');
            location.reload(); // Reload halaman untuk update data
        } else {
            alert('Gagal menandai transaksi sebagai lunas: ' + (result.message || 'Terjadi kesalahan'));
        }
    } catch (error) {
        console.error('Error marking transaction as paid:', error);
        alert('Gagal menandai transaksi sebagai lunas. Silakan coba lagi.');
    }
}

// Toggle filter visibility
function toggleFilter() {
    const filterContainer = document.getElementById('filterFormContainer');
    const toggleBtn = document.getElementById('toggleFilterBtn');
    const toggleText = document.getElementById('filterToggleText');
    const icon = toggleBtn.querySelector('i');
    
    if (filterContainer.style.display === 'none') {
        // Show filter
        filterContainer.style.display = 'block';
        toggleText.textContent = 'Sembunyikan Filter';
        icon.className = 'fas fa-chevron-up mr-1';
    } else {
        // Hide filter
        filterContainer.style.display = 'none';
        toggleText.textContent = 'Tampilkan Filter';
        icon.className = 'fas fa-chevron-down mr-1';
    }
}

// Initialize filter state on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check if filter has values to determine initial state
    const hasFilterValues = document.querySelector('input[name="start_date"]').value || 
                           document.querySelector('input[name="end_date"]').value ||
                           document.querySelector('select[name="status"]').value !== 'all' ||
                           document.querySelector('select[name="customer_type"]').value !== 'all';
    
    // If no filter values, hide filter by default for cleaner UI
    if (!hasFilterValues) {
        const filterContainer = document.getElementById('filterFormContainer');
        const toggleBtn = document.getElementById('toggleFilterBtn');
        const toggleText = document.getElementById('filterToggleText');
        const icon = toggleBtn.querySelector('i');
        
        filterContainer.style.display = 'none';
        toggleText.textContent = 'Tampilkan Filter';
        icon.className = 'fas fa-chevron-down mr-1';
    }
});
</script>

@endsection
