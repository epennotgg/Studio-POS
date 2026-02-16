 @extends('layouts.app')

@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- User Profile Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900">Profil Pengguna</h3>
        </div> 
        <div class="card-body">
            <div class="flex flex-col items-center text-center">
                <div class="w-24 h-24 flex items-center justify-center rounded-full mr-6 bg-blue-100 text-blue-800 text-3xl font-bold mb-4">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div class="mb-4 mr-6">
                    <div class="text-xl font-bold text-gray-900">{{ $user->name }}</div>
                    <div class="text-sm text-gray-600">Username: {{ $user->username }}</div>
                    <div class="text-sm text-gray-600 mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-badge text-xs font-medium {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </div>
                </div>
                <div class="text-sm mr-6 text-gray-500">
                    Bergabung: {{ $user->created_at->format('d F Y') }}
                </div>
            </div>
        </div>
    </div>
    
    <!-- Change PIN Form -->
    <div class="card lg:col-span-2">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900">Ubah PIN</h3>
        </div>
        <div class="card-body">
            @if(session('success'))
            <div class="alert alert-success mb-4">
                {{ session('success') }}
            </div>
            @endif
            
            @if($errors->any())
            <div class="alert alert-danger mb-4">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            
            <form method="POST" action="{{ route('settings.changePin') }}">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="form-group">
                        <label class="form-label">PIN Saat Ini</label>
                        <input type="password" name="current_pin" class="form-control" required>
                        @error('current_pin')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">PIN Baru (4-6 digit)</label>
                        <input type="password" name="new_pin" class="form-control" required minlength="4" maxlength="6">
                        @error('new_pin')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Konfirmasi PIN Baru</label>
                        <input type="password" name="new_pin_confirmation" class="form-control" required minlength="4" maxlength="6">
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-lock mr-1"></i> PIN digunakan untuk login ke sistem
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(Auth::user()->role === 'admin')
<!-- Admin Tools -->
<div class="card">
    <div class="card-header">
        <h3 class="text-lg font-semibold text-gray-900">Administrator Tools</h3>
    </div>
    <div class="card-body">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="{{ route('employee.index') }}" class="flex flex-col items-center justify-center p-6 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                <div class="text-3xl mb-2 mx-3 text-purple-800 bigicon"><i class="fas fa-users"></i></div>
                <div class="font-medium text-gray-900 card-text mx-3">Manajemen Karyawan</div>
                <div class="text-sm text-gray-600 mx-3 card-text mt-1">Tambah, edit, hapus karyawan</div>
            </a>
            
            <a href="{{ route('products.index') }}" class="flex flex-col items-center justify-center p-6 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                <div class="text-3xl mb-2 mx-3 text-blue-800 bigicon"><i class="fas fa-boxes"></i></div>
                <div class="font-medium text-gray-900 card-text mx-3">Manajemen Produk</div>
                <div class="text-sm text-gray-600 mt-1 card-text mx-3">Kelola stok dan harga produk</div>
            </a>
            
            <a href="{{ route('transaction.history') }}" class="flex flex-col items-center justify-center p-6 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                <div class="text-3xl mb-2 mx-3 text-green-800 bigicon"><i class="fas fa-chart-bar"></i></div>
                <div class="font-medium text-gray-900 card-text mx-3">Laporan Transaksi</div>
                <div class="text-sm text-gray-600 mt-1 card-text mx-3">Analisis dan export data</div>
            </a>
        </div>
    </div>
</div>
@endif

<style>
    .grid {
        display: grid;
    }
    
    .grid-cols-1 {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
    
    .sm\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    @media (min-width: 768px) {
        .md\:grid-cols-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        
        .md\:grid-cols-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    
    @media (min-width: 1024px) {
        .lg\:grid-cols-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        
        .lg\:col-span-2 {
            grid-column: span 2 / span 2;
        }
    }
    
    .gap-4 {
        gap: 1rem;
    }
    
    .gap-6 {
        gap: 1.5rem;
    }
    
    .text-3xl {
        font-size: 1.875rem;
        line-height: 2.25rem;
    }
    
    .bg-blue-100 {
        background-color: #dbeafe;
    }
    
    .text-blue-800 {
        color: #1e40af;
    }
    
    .bg-purple-100 {
        background-color: #e9d5ff;
    }
    
    .text-purple-800 {
        color: #5b21b6;
    }

    .text-green-800 {
        color: #065f46;
    }
    
    .bg-purple-50 {
        background-color: #faf5ff;
    }
    
    .bg-blue-50 {
        background-color: #eff6ff;
    }
    
    .mr-6 {
        margin-right: 1rem;
    }

    .bg-green-50 {
        background-color: #f0fdf4;
    }
    
    .bg-gray-50 {
        background-color: #f9fafb;
    }
    
    .text-red-600 {
        color: #dc2626;
    }
    
    .transition-colors {
        transition-property: background-color, border-color, color, fill, stroke;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 150ms;
    }

    /* Dark Mode Overrides for Admin Tools */
    .dark-mode .bg-purple-50 {
        background-color: #44337a;
        border: 1px solid #553c9a;
    }
    .dark-mode .bg-purple-50:hover {
        background-color: #553c9a;
    }
    
    .dark-mode .bg-blue-50 {
        background-color: #2c5282;
        border: 1px solid #2b6cb0;
    }
    .dark-mode .bg-blue-50:hover {
        background-color: #2b6cb0;
    }
    
    .dark-mode .bg-green-50 {
        background-color: #22543d;
        border: 1px solid #276749;
    }
    .dark-mode .bg-green-50:hover {
        background-color: #276749;
    }

    .dark-mode .card-text {
        color: #e2e8f0 !important;
    }

    .dark-mode .text-purple-800.bigicon {
        color: #e9d5ff !important;
    }

    .dark-mode .text-blue-800.bigicon {
        color: #dbeafe !important;
    }
    
    .dark-mode .text-green-800.bigicon {
        color: #c6f6d5 !important;
    }
    
    /* Toggle switch styles */
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border-width: 0;
    }
    
    .peer:checked ~ .peer-checked\:bg-blue-600 {
        background-color: #2563eb;
    }
    
    .peer:checked ~ .peer-checked\:bg-green-600 {
        background-color: #059669;
    }
    
    .peer:checked ~ .peer-checked\:bg-yellow-600 {
        background-color: #d97706;
    }
    
    .peer:checked ~ .peer-checked\:after\:translate-x-full:after {
        transform: translateX(100%);
    }
    
    .peer-focus\:ring-4:focus {
        --tw-ring-opacity: 1;
        --tw-ring-color: rgba(59, 130, 246, var(--tw-ring-opacity));
    }
    
    .peer-focus\:ring-blue-300:focus {
        --tw-ring-color: rgba(147, 197, 253, 0.5);
    }
</style>

<script>
    // Dark/Light Mode Toggle
    const darkModeToggle = document.getElementById('darkModeToggle');
    const lightModeToggle = document.getElementById('lightModeToggle');
    
    // Load saved theme preference
    const savedTheme = localStorage.getItem('theme') || 'light';
    if (savedTheme === 'dark') {
        darkModeToggle.checked = true;
        lightModeToggle.checked = false;
        document.documentElement.classList.add('dark-mode');
    } else {
        darkModeToggle.checked = false;
        lightModeToggle.checked = true;
        document.documentElement.classList.remove('dark-mode');
    }
    
    // Dark mode toggle
    darkModeToggle.addEventListener('change', function() {
        if (this.checked) {
            lightModeToggle.checked = false;
            document.documentElement.classList.add('dark-mode');
            localStorage.setItem('theme', 'dark');
            showToast('Mode gelap diaktifkan', 'success');
        } else {
            lightModeToggle.checked = true;
            document.documentElement.classList.remove('dark-mode');
            localStorage.setItem('theme', 'light');
            showToast('Mode terang diaktifkan', 'info');
        }
    });
    
    // Light mode toggle
    lightModeToggle.addEventListener('change', function() {
        if (this.checked) {
            darkModeToggle.checked = false;
            document.documentElement.classList.remove('dark-mode');
            localStorage.setItem('theme', 'light');
            showToast('Mode terang diaktifkan', 'info');
        } else {
            darkModeToggle.checked = true;
            document.documentElement.classList.add('dark-mode');
            localStorage.setItem('theme', 'dark');
            showToast('Mode gelap diaktifkan', 'success');
        }
    });
    
    
    function showToast(message, type = 'info') {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-4 py-3 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-100 text-green-800 border border-green-200' :
            type === 'error' ? 'bg-red-100 text-red-800 border border-red-200' :
            'bg-blue-100 text-blue-800 border border-blue-200'
        }`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        // Remove toast after 3 seconds
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
</script>
@endsection
