@extends('layouts.app')

@section('title', 'Manajemen Produk')
@section('page-title', 'Manajemen Produk')

@section('header-actions')
    <button type="button" class="btn btn-success" onclick="openModal('createProductModal')">
        + Tambah Produk
    </button>
    <a href="{{ route('transaction.index') }}" class="btn btn-primary">
        💰 Ke Kasir
    </a>
@endsection

@section('content')
<div class="card mb-6">
    <div class="card-header flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900">Daftar Produk</h3>
        <div class="flex items-center gap-2">
            <input type="text" id="productSearch" class="form-control" placeholder="Cari produk..." style="width: 200px;">
            <button type="button" class="btn btn-primary" onclick="searchProducts()">🔍</button>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        
        <div class="table-responsive">
            <table class="table" id="productsTable">
                <thead>
                    <tr>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Harga Umum</th>
                        <th>Harga Agen 1</th>
                        <th>Harga Agen 2</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr data-product-id="{{ $product->id }}">
                        <td>
                            <div class="font-medium">{{ $product->name }}</div>
                            <div class="text-sm text-gray-600">{{ $product->type_color ?? '-' }}</div>
                            @if($product->barcode)
                            <div class="text-xs text-gray-500">Barcode: {{ $product->barcode }}</div>
                            @endif
                        </td>
                        <td>{{ $product->category->name }}</td>
                        <td>
                            <span class="font-medium {{ $product->stock <= 5 ? 'text-red-600' : '' }}">
                                {{ $product->stock }}
                            </span>
                            @if($product->stock <= 5)
                            <div class="text-xs text-red-500">Stok rendah!</div>
                            @endif
                        </td>
                        <td class="font-medium">Rp {{ number_format($product->price_general, 0, ',', '.') }}</td>
                        <td class="font-medium">Rp {{ number_format($product->price_agent1, 0, ',', '.') }}</td>
                        <td class="font-medium">Rp {{ number_format($product->price_agent2, 0, ',', '.') }}</td>
                        <td>
                            <div class="flex gap-2">
                                <button type="button" class="btn btn-warning text-sm" onclick="editProduct({{ $product->id }})">
                                    Edit
                                </button>
                                <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Yakin hapus produk ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger text-sm">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($products->isEmpty())
        <div class="text-center py-8 text-gray-600">
            Tidak ada produk. Klik "Tambah Produk" untuk menambahkan produk pertama.
        </div>
        @endif
    </div>
</div>

<!-- Create Product Modal -->
<div class="modal" id="createProductModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="text-lg font-semibold text-gray-900">Tambah Produk Baru</h3>
            <button type="button" onclick="closeModal('createProductModal')" class="btn btn-danger">✕</button>
        </div>
        <form action="{{ route('products.store') }}" method="POST" id="createProductForm">
            @csrf
            <div class="modal-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Nama Produk *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Kategori *</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Tipe/Warna</label>
                        <input type="text" name="type_color" class="form-control" placeholder="Contoh: Merah, 4R, Gold">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Barcode (Opsional)</label>
                        <input type="text" name="barcode" class="form-control" placeholder="Kode barcode">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Stok Awal *</label>
                        <input type="number" name="stock" class="form-control" value="0" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Harga Beli *</label>
                        <input type="number" name="buy_price" class="form-control" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Harga Umum *</label>
                        <input type="number" name="price_general" class="form-control" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Harga Agen 1 *</label>
                        <input type="number" name="price_agent1" class="form-control" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Harga Agen 2 *</label>
                        <input type="number" name="price_agent2" class="form-control" step="0.01" min="0" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('createProductModal')">Batal</button>
                <button type="submit" class="btn btn-success">Simpan Produk</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal" id="editProductModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="text-lg font-semibold text-gray-900">Edit Produk</h3>
            <button type="button" onclick="closeModal('editProductModal')" class="btn btn-danger">✕</button>
        </div>
        <form method="POST" id="editProductForm">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Nama Produk *</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Kategori *</label>
                        <select name="category_id" id="edit_category_id" class="form-control" required>
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Tipe/Warna</label>
                        <input type="text" name="type_color" id="edit_type_color" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Barcode (Opsional)</label>
                        <input type="text" name="barcode" id="edit_barcode" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Stok *</label>
                        <input type="number" name="stock" id="edit_stock" class="form-control" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Harga Beli *</label>
                        <input type="number" name="buy_price" id="edit_buy_price" class="form-control" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Harga Umum *</label>
                        <input type="number" name="price_general" id="edit_price_general" class="form-control" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Harga Agen 1 *</label>
                        <input type="number" name="price_agent1" id="edit_price_agent1" class="form-control" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Harga Agen 2 *</label>
                        <input type="number" name="price_agent2" id="edit_price_agent2" class="form-control" step="0.01" min="0" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editProductModal')">Batal</button>
                <button type="submit" class="btn btn-success">Update Produk</button>
            </div>
        </form>
    </div>
</div>

<style>
    .grid {
        display: grid;
    }
    
    .grid-cols-1 {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
    
    @media (min-width: 768px) {
        .md\:grid-cols-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    
    .gap-4 {
        gap: 1rem;
    }
    
    .text-red-600 {
        color: #dc2626;
    }
    
    .text-red-500 {
        color: #ef4444;
    }
</style>

<script>
    let allProducts = JSON.parse('{!! json_encode($products) !!}');
    
    function searchProducts() {
        const searchTerm = document.getElementById('productSearch').value.toLowerCase();
        const rows = document.querySelectorAll('#productsTable tbody tr');
        
        rows.forEach(row => {
            const productName = row.querySelector('td:first-child .font-medium').textContent.toLowerCase();
            const typeColor = row.querySelector('td:first-child .text-gray-600').textContent.toLowerCase();
            const barcode = row.querySelector('td:first-child .text-gray-500')?.textContent.toLowerCase() || '';
            
            const matches = productName.includes(searchTerm) || 
                           typeColor.includes(searchTerm) || 
                           barcode.includes(searchTerm);
            
            row.style.display = matches ? '' : 'none';
        });
    }
    
    // Handle Enter key in search
    document.getElementById('productSearch').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchProducts();
        }
    });
    
    async function editProduct(productId) {
        try {
            const response = await fetch(`/products/${productId}/edit`);
            const html = await response.text();
            
            // Create a temporary div to parse the HTML
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            
            // Extract form data from the edit page
            const form = tempDiv.querySelector('form');
            if (!form) {
                alert('Gagal memuat data produk');
                return;
            }
            
            // Populate the modal form
            document.getElementById('edit_name').value = tempDiv.querySelector('input[name="name"]')?.value || '';
            document.getElementById('edit_category_id').value = tempDiv.querySelector('select[name="category_id"]')?.value || '';
            document.getElementById('edit_type_color').value = tempDiv.querySelector('input[name="type_color"]')?.value || '';
            document.getElementById('edit_barcode').value = tempDiv.querySelector('input[name="barcode"]')?.value || '';
            document.getElementById('edit_stock').value = tempDiv.querySelector('input[name="stock"]')?.value || 0;
            document.getElementById('edit_buy_price').value = tempDiv.querySelector('input[name="buy_price"]')?.value || 0;
            document.getElementById('edit_price_general').value = tempDiv.querySelector('input[name="price_general"]')?.value || 0;
            document.getElementById('edit_price_agent1').value = tempDiv.querySelector('input[name="price_agent1"]')?.value || 0;
            document.getElementById('edit_price_agent2').value = tempDiv.querySelector('input[name="price_agent2"]')?.value || 0;
            
            // Update form action
            document.getElementById('editProductForm').action = `/products/${productId}`;
            
            // Show modal
            openModal('editProductModal');
            
        } catch (error) {
            console.error('Error loading product:', error);
            alert('Gagal memuat data produk');
        }
    }
    
    // Handle form submissions
    document.getElementById('createProductForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (response.ok) {
                window.location.reload();
            } else {
                return response.text().then(text => {
                    throw new Error(text);
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal menyimpan produk');
        });
    });
    
    document.getElementById('editProductForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-HTTP-Method-Override': 'PUT'
            }
        })
        .then(response => {
            if (response.ok) {
                window.location.reload();
            } else {
                return response.text().then(text => {
                    throw new Error(text);
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal mengupdate produk');
        });
    });
    
    // Auto-focus on first input when modal opens
    document.addEventListener('DOMContentLoaded', function() {
        const createModal = document.getElementById('createProductModal');
        const editModal = document.getElementById('editProductModal');
        
        createModal.addEventListener('modal.open', function() {
            setTimeout(() => {
                const firstInput = createModal.querySelector('input, select');
                if (firstInput) firstInput.focus();
            }, 100);
        });
        
        editModal.addEventListener('modal.open', function() {
            setTimeout(() => {
                const firstInput = editModal.querySelector('input, select');
                if (firstInput) firstInput.focus();
            }, 100);
        });
    });
</script>
@endsection