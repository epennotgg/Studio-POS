@extends('layouts.app')

@section('title', 'Riwayat Transaksi')
@section('page-title', 'Riwayat Transaksi')

@section('header-actions')
    <button type="button" class="btn btn-success" onclick="exportToExcel()">
        <i class="fas fa-file-excel mr-1"></i> Export Excel
    </button>
@endsection

@section('content')
<style>
    /* Pagination styles */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-top: 24px;
        padding: 16px 0;
    }
    
    .pagination .page-item {
        list-style: none;
    }
    
    .pagination .page-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 0 12px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        background-color: white;
        color: #374151;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    
    .pagination .page-link:hover {
        background-color: #f3f4f6;
        border-color: #d1d5db;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }
    
    .pagination .page-item.disabled .page-link {
        background-color: #f9fafb;
        border-color: #e5e7eb;
        color: #9ca3af;
        cursor: not-allowed;
    }
    
    .pagination .page-item:first-child .page-link,
    .pagination .page-item:last-child .page-link {
        min-width: 80px;
    }
    
    /* SVG styling for pagination icons */
    .pagination svg {
        width: 1.25rem;
        height: 1.25rem;
        fill: currentColor;
    }
    
    .pagination .page-link svg {
        vertical-align: middle;
    }
    
    /* Simple CSS for Laravel pagination - override default Tailwind */
    .pagination > div {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-top: 24px;
        padding: 16px 0;
    }
    
    .pagination > div > div {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .pagination span[aria-disabled="true"] span,
    .pagination span[aria-current="page"] span,
    .pagination a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 0 12px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        background-color: white;
        color: #374151;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    
    .pagination a:hover {
        background-color: #f3f4f6;
        border-color: #d1d5db;
    }
    
    .pagination span[aria-current="page"] span {
        background-color: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }
    
    .pagination span[aria-disabled="true"] span {
        background-color: #f9fafb;
        border-color: #e5e7eb;
        color: #9ca3af;
        cursor: not-allowed;
    }
    
    .pagination svg {
        width: 1.25rem;
        height: 1.25rem;
        fill: currentColor;
    }
    
    /* Fix untuk icon pagination yang terlalu besar */
    nav[role="navigation"] svg {
        width: 20px;
        height: 20px;
    }
    
    /* Sembunyikan tombol Previous/Next text yang duplikat */
    nav[role="navigation"] > div:first-child {
        display: none;
    }
</style>

<div class="card mb-6">
    <div class="card-header">
        <h3 class="text-lg font-semibold text-gray-900">Filter Transaksi</h3>
    </div>
    <div class="card-body">
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
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
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
                                @if($transaction->status === 'paid' && Auth::user()->role === 'admin')
                                <button type="button" class="btn btn-danger text-sm" onclick="cancelTransaction({{ $transaction->id }})">
                                    <i class="fas fa-times-circle mr-1"></i> Batal
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
</script>

@endsection
