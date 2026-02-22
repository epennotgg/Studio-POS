@extends('layouts.app')

@section('title', 'Kasir')
@section('page-title', 'Kasir')

@section('header-actions')
    <button type="button" class="btn btn-primary" onclick="openModal('productSearchModal')">
        🔍 Cari Produk
    </button>
@endsection

@section('content')
<div class="card mb-6">
    <div class="card-header">
        <h3 class="text-lg font-semibold text-gray-900">Informasi Pelanggan</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('transaction.store') }}" method="POST" id="checkoutForm">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="form-group">
                    <label class="form-label">Nama Pelanggan</label>
                    <input type="text" name="customer_name" class="form-control" placeholder="Contoh: Budi" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Nomor HP (Opsional)</label>
                    <input type="text" name="customer_phone" class="form-control" placeholder="0812xxxx">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Tipe Pelanggan</label>
                    <select name="customer_type" class="form-control" required>
                        <option value="umum">Umum</option>
                        <option value="agen1">Agen 1</option>
                        <option value="agen2">Agen 2</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Metode Pembayaran</label>
                    <select name="payment_method" class="form-control" required>
                        <option value="Cash">Tunai (Cash)</option>
                        <option value="Transfer">Transfer Bank</option>
                        <option value="QRIS">QRIS</option>
                    </select>
                </div>
            </div>
            
            <!-- DP/Pending Transaction Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="form-group">
                    <div class="flex items-center">
                        <input type="checkbox" id="is_dp" name="is_dp" value="1" class="mr-2" onchange="toggleDPField()">
                        <label for="is_dp" class="form-label mb-0">Tandai sebagai DP (Down Payment)</label>
                    </div>
                </div>
                
                <div class="form-group" id="dp_amount_group" style="display: none;">
                    <label class="form-label">Jumlah DP</label>
                    <input type="number" id="down_payment" name="down_payment" class="form-control" min="0" placeholder="Masukkan jumlah DP">
                    <div class="text-sm text-gray-600 mt-1" id="dp_info"></div>
                </div>
            </div>
            
            <hr class="mb-6">
            
            <!-- Keranjang Belanja -->
            <div class="card mb-6">
                <div class="card-header flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Keranjang Belanja</h3>
                    <div class="text-lg font-semibold">
                        Total: <span id="totalAmount">Rp 0</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="cartTable">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th width="120">Harga</th>
                                    <th width="100">Qty</th>
                                    <th width="120">Subtotal</th>
                                    <th width="80">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="cartTableBody">
                                <!-- Items will be added here via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div id="hiddenInputs">
                        <!-- Hidden inputs for form submission -->
                    </div>
                    
                    <div class="text-center mt-6" id="emptyCartMessage">
                        <p class="text-gray-600 mb-4">Keranjang belanja masih kosong</p>
                        <button type="button" class="btn btn-primary" onclick="openModal('productSearchModal')">
                            + Tambah Produk
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="btn btn-success text-lg px-8 py-3" id="checkoutButton" disabled>
                    💳 BAYAR SEKARANG
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Popular Products Section -->
<div class="card mb-6">
    <div class="card-header">
        <h3 class="text-lg font-semibold text-gray-900">Produk Paling Populer</h3>
    </div>
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4" id="popularProductsGrid">
            <!-- Popular products will be loaded here -->
        </div>
    </div>
</div>

<!-- Product Search Modal -->
<div class="modal" id="productSearchModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="text-lg font-semibold text-gray-900">Cari Produk</h3>
            <button type="button" onclick="closeModal('productSearchModal')" class="btn btn-danger">✕</button>
        </div>
        <div class="modal-body">
            <div class="form-group mb-4">
                <input type="text" id="productSearchInput" class="form-control" 
                       placeholder="Ketik nama produk untuk mencari..." 
                       onkeyup="searchProducts()">
                <div class="text-sm text-gray-600 mt-1">
                    Tekan Enter untuk menambahkan produk pertama dari hasil pencarian
                </div>
            </div>
            
            <div id="searchResults" class="space-y-2 max-h-96 overflow-y-auto">
                <!-- Search results will appear here -->
            </div>
            
            <div id="noResults" class="text-center text-gray-600 py-8" style="display: none;">
                Tidak ada produk yang ditemukan
            </div>
        </div>
    </div>
</div>

<!-- Product Quantity Modal -->
<div class="modal" id="quantityModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="text-lg font-semibold text-gray-900" id="quantityProductName"></h3>
            <button type="button" onclick="closeModal('quantityModal')" class="btn btn-danger">✕</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Jumlah</label>
                <input type="number" id="quantityInput" class="form-control" value="1" min="1">
                <div class="text-sm text-gray-600 mt-1" id="quantityStockInfo"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('quantityModal')">Batal</button>
                <button type="button" class="btn btn-primary" onclick="addProductToCart()">Tambah ke Keranjang</button>
            </div>
        </div>
    </div>
</div>

<!--
<style>
    .grid {
        display: grid;
    }
    
    .grid-cols-1 {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
    
    @media (min-width: 768px) {
        .md\:grid-cols-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    
    .space-y-2 > * + * {
        margin-top: 0.5rem;
    }
    
    .max-h-96 {
        max-height: 24rem;
    }
    
    .overflow-y-auto {
        overflow-y: auto;
    }
    
    /* Styles for popular products */
    #popularProductsGrid > div {
        min-height: 100px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 12px;
        transition: all 0.2s ease;
    }
    
    #popularProductsGrid > div:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .popular-product-name {
        font-size: 14px;
        line-height: 1.3;
        margin-bottom: 8px;
        margin-left: 4px;
        margin-right: 4px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        min-height: 34px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .product-type {
        font-size: 11px;
        margin-bottom: 4px;
        margin-left: 4px;
        margin-right: 4px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .popular-product-price {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 4px;
        margin-left: 4px;
        margin-right: 4px;
    }
    
    .popular-product-stock {
        font-size: 11px;
        color: #6b7280;
        margin-left: 4px;
        margin-right: 4px;
    }
    
    /* Styles for DP checkbox */
    #is_dp {
        width: 20px;
        height: 20px;
        margin-right: 10px;
        vertical-align: middle;
        cursor: pointer;
    }
    
    #is_dp + label {
        cursor: pointer;
        vertical-align: middle;
        font-size: 16px;
        font-weight: 500;
        color: #374151;
    }
    
    #is_dp:checked {
        accent-color: #3b82f6;
    }
    
    /* DP amount input styling */
    #down_payment {
        font-size: 16px;
        padding: 10px;
        border: 2px solid #d1d5db;
        border-radius: 6px;
        transition: border-color 0.2s ease;
        background-color: white;
        color: #374151;
    }
    
    #down_payment:focus {
        border-color: #3b82f6;
        outline: none;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    #dp_info {
        font-size: 14px;
        line-height: 1.5;
        padding: 8px;
        background-color: #f9fafb;
        border-radius: 4px;
        border-left: 3px solid #3b82f6;
        color: #374151;
    }
    
    /* Dark mode support for DP elements */
    .dark-mode #is_dp + label {
        color: #e5e7eb;
    }
    
    .dark-mode #down_payment {
        background-color: #374151;
        border-color: #4b5563;
        color: #e5e7eb;
    }
    
    .dark-mode #down_payment:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
    }
    
    .dark-mode #dp_info {
        background-color: #374151;
        color: #e5e7eb;
        border-left-color: #3b82f6;
    }
    
    .dark-mode #is_dp:checked {
        accent-color: #60a5fa;
    }
    
    /* Dark mode support for popular products */
    .dark-mode #popularProductsGrid > div {
        background-color: #374151;
    }
    
    .dark-mode #popularProductsGrid > div:hover {
        background-color: #4b5563;
    }
    
    .dark-mode .popular-product-name {
        color: #e5e7eb;
    }
    
    .dark-mode .product-type {
        color: #9ca3af;
    }
    
    .dark-mode .popular-product-price {
        color: #60a5fa;
    }
    
    .dark-mode .popular-product-stock {
        color: #9ca3af;
    }
    
    /* Dark mode support for search results */
    .dark-mode #searchResults > div {
        background-color: #374151;
        color: #e5e7eb;
    }
    
    .dark-mode #searchResults > div:hover {
        background-color: #4b5563;
    }
    
    .dark-mode #searchResults > div .text-gray-600 {
        color: #9ca3af;
    }
    
    /* Dark mode support for form elements */
    .dark-mode .form-control {
        background-color: #374151;
        border-color: #4b5563;
        color: #e5e7eb;
    }
    
    .dark-mode .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
    }
    
    .dark-mode .form-label {
        color: #e5e7eb;
    }
    
    .dark-mode .text-gray-600 {
        color: #9ca3af !important;
    }
    
    /* Dark mode support for table */
    .dark-mode .table {
        color: #e5e7eb;
    }
    
    .dark-mode .table thead th {
        background-color: #374151;
        border-color: #4b5563;
        color: #e5e7eb;
    }
    
    .dark-mode .table tbody td {
        border-color: #4b5563;
    }
    
    .dark-mode .table tbody tr:hover {
        background-color: #4b5563;
    }
</style>
-->

<script>
    let cart = [];
    let allProducts = JSON.parse('{!! json_encode($products) !!}');
    let popularProducts = JSON.parse('{!! json_encode($popularProducts) !!}');
    let selectedProduct = null;
    
    // Load popular products on page load
    function loadPopularProducts() {
        const popularProductsGrid = document.getElementById('popularProductsGrid');
        popularProductsGrid.innerHTML = '';
        
        if (!popularProducts || popularProducts.length === 0) {
            popularProductsGrid.innerHTML = '<div class="col-span-full text-center text-gray-600 py-4">Belum ada data produk populer</div>';
            return;
        }
        
        popularProducts.forEach(product => {
            const price = getPriceForCustomerType(product);
            const productElement = document.createElement('div');
            productElement.className = 'bg-gray-50 rounded-lg hover:bg-gray-100 cursor-pointer transition-all duration-200 hover:shadow-md';
            productElement.onclick = () => selectProduct(product);
            productElement.title = `Klik untuk menambahkan ${product.name}`;
            
            productElement.innerHTML = `
                <div class="p-3 flex flex-col h-full">
                    <div class="popular-product-name mx-3">${product.name}</div>
                    <div class="text-gray-600 product-type truncate">${product.type_color || ''}</div>
                    <div class="mt-auto">
                        <div class="popular-product-price">Rp ${formatNumber(price)}</div>
                        <div class="popular-product-stock">Stok: ${product.stock}</div>
                    </div>
                </div>
            `;
            
            popularProductsGrid.appendChild(productElement);
        });
    }
    
    // Product search without minimum character limit - search by name, type/color, and barcode
    function searchProducts() {
        const searchInput = document.getElementById('productSearchInput');
        const searchTerm = searchInput.value.toLowerCase().trim();
        const searchResults = document.getElementById('searchResults');
        const noResults = document.getElementById('noResults');
        
        searchResults.innerHTML = '';
        
        if (searchTerm.length === 0) {
            noResults.style.display = 'none';
            return;
        }
        
        const filteredProducts = allProducts.filter(product => {
            // Search in name
            const nameMatch = product.name.toLowerCase().includes(searchTerm);
            
            // Search in type/color
            const typeMatch = product.type_color && product.type_color.toLowerCase().includes(searchTerm);
            
            // Search in barcode (exact match or partial)
            const barcodeMatch = product.barcode && product.barcode.toLowerCase().includes(searchTerm);
            
            // Search in barcode with exact match for numeric search
            const exactBarcodeMatch = product.barcode && product.barcode === searchTerm;
            
            return nameMatch || typeMatch || barcodeMatch || exactBarcodeMatch;
        });
        
        if (filteredProducts.length === 0) {
            noResults.style.display = 'block';
            return;
        }
        
        noResults.style.display = 'none';
        
        filteredProducts.forEach(product => {
            const price = getPriceForCustomerType(product);
            const productElement = document.createElement('div');
            productElement.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 cursor-pointer';
            productElement.onclick = () => selectProduct(product);
            productElement.onkeypress = (e) => {
                if (e.key === 'Enter') selectProduct(product);
            };
            productElement.tabIndex = 0;
            
            // Highlight which field matched
            let matchInfo = '';
            if (product.name.toLowerCase().includes(searchTerm)) {
                matchInfo = 'Nama';
            } else if (product.type_color && product.type_color.toLowerCase().includes(searchTerm)) {
                matchInfo = 'Tipe';
            } else if (product.barcode && (product.barcode.toLowerCase().includes(searchTerm) || product.barcode === searchTerm)) {
                matchInfo = 'Barcode';
            }
            
            productElement.innerHTML = `
                <div>
                    <div class="font-medium">${product.name}</div>
                    <div class="text-sm text-gray-600">
                        ${product.type_color || ''} 
                        ${product.barcode ? `• Barcode: ${product.barcode}` : ''}
                        • Stok: ${product.stock}
                        ${matchInfo ? `<span class="text-xs bg-blue-100 text-blue-800 px-1 rounded ml-1">${matchInfo}</span>` : ''}
                    </div>
                </div>
                <div class="text-right">
                    <div class="font-semibold">Rp ${formatNumber(price)}</div>
                    <div class="text-sm text-gray-600">${getCustomerTypeLabel()}</div>
                </div>
            `;
            
            searchResults.appendChild(productElement);
        });
        
        // Auto-select first result if Enter is pressed in search input
        document.getElementById('productSearchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const firstResult = document.querySelector('#searchResults > div');
                if (firstResult) {
                    firstResult.click();
                }
            }
        });
    }
    
    // Handle Enter key in search input
    document.getElementById('productSearchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const firstResult = document.querySelector('#searchResults > div');
            if (firstResult) {
                firstResult.click();
            }
        }
    });
    
    function selectProduct(product) {
        selectedProduct = product;
        document.getElementById('quantityProductName').textContent = product.name;
        document.getElementById('quantityStockInfo').textContent = `Stok tersedia: ${product.stock}`;
        document.getElementById('quantityInput').value = 1;
        document.getElementById('quantityInput').max = product.stock;
        closeModal('productSearchModal');
        openModal('quantityModal');
        document.getElementById('quantityInput').focus();
    }
    
    function addProductToCart() {
        if (!selectedProduct) return;
        
        const quantity = parseInt(document.getElementById('quantityInput').value);
        const customerType = document.querySelector('select[name="customer_type"]').value;
        
        if (quantity > selectedProduct.stock) {
            alert(`Stok tidak mencukupi! Sisa stok: ${selectedProduct.stock}`);
            return;
        }
        
        const price = getPriceForCustomerType(selectedProduct, customerType);
        const subtotal = price * quantity;
        
        // Check if product already in cart
        const existingIndex = cart.findIndex(item => item.id === selectedProduct.id);
        
        if (existingIndex > -1) {
            // Update existing item
            cart[existingIndex].qty += quantity;
            cart[existingIndex].subtotal = cart[existingIndex].qty * price;
        } else {
            // Add new item
            cart.push({
                id: selectedProduct.id,
                name: selectedProduct.name,
                type_color: selectedProduct.type_color,
                price: price,
                qty: quantity,
                subtotal: subtotal
            });
        }
        
        closeModal('quantityModal');
        renderCart();
        document.getElementById('productSearchInput').value = '';
    }
    
    function getPriceForCustomerType(product) {
        const customerType = document.querySelector('select[name="customer_type"]').value;
        switch(customerType) {
            case 'agen1': return product.price_agent1;
            case 'agen2': return product.price_agent2;
            default: return product.price_general;
        }
    }
    
    function getCustomerTypeLabel() {
        const customerType = document.querySelector('select[name="customer_type"]').value;
        switch(customerType) {
            case 'agen1': return 'Agen 1';
            case 'agen2': return 'Agen 2';
            default: return 'Umum';
        }
    }
    
    function renderCart() {
        const tbody = document.getElementById('cartTableBody');
        const hiddenDiv = document.getElementById('hiddenInputs');
        const emptyCartMessage = document.getElementById('emptyCartMessage');
        const checkoutButton = document.getElementById('checkoutButton');
        const totalAmount = document.getElementById('totalAmount');
        
        tbody.innerHTML = '';
        hiddenDiv.innerHTML = '';
        
        if (cart.length === 0) {
            emptyCartMessage.style.display = 'block';
            checkoutButton.disabled = true;
            totalAmount.textContent = 'Rp 0';
            return;
        }
        
        emptyCartMessage.style.display = 'none';
        checkoutButton.disabled = false;
        
        let grandTotal = 0;
        
        cart.forEach((item, index) => {
            grandTotal += item.subtotal;
            
            // Add to table
            tbody.innerHTML += `
                <tr>
                    <td>
                        <div class="font-medium">${item.name}</div>
                        <div class="text-sm text-gray-600">${item.type_color || ''}</div>
                    </td>
                    <td class="font-medium">Rp ${formatNumber(item.price)}</td>
                    <td>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="updateQuantity(${index}, -1)" class="btn btn-warning text-sm px-2">-</button>
                            <span class="font-medium">${item.qty}</span>
                            <button type="button" onclick="updateQuantity(${index}, 1)" class="btn btn-warning text-sm px-2">+</button>
                        </div>
                    </td>
                    <td class="font-medium">Rp ${formatNumber(item.subtotal)}</td>
                    <td>
                        <button type="button" onclick="removeFromCart(${index})" class="btn btn-danger text-sm">
                            Hapus
                        </button>
                    </td>
                </tr>
            `;
            
            // Add hidden inputs
            hiddenDiv.innerHTML += `
                <input type="hidden" name="cart[${index}][id]" value="${item.id}">
                <input type="hidden" name="cart[${index}][qty]" value="${item.qty}">
            `;
        });
        
        // Add total amount hidden input
        hiddenDiv.innerHTML += `<input type="hidden" name="total_amount" value="${grandTotal}">`;
        totalAmount.textContent = `Rp ${formatNumber(grandTotal)}`;
    }
    
    function updateQuantity(index, change) {
        const product = allProducts.find(p => p.id == cart[index].id);
        if (!product) return;
        
        const newQty = cart[index].qty + change;
        
        if (newQty < 1) {
            removeFromCart(index);
            return;
        }
        
        if (newQty > product.stock) {
            alert(`Stok tidak mencukupi! Sisa stok: ${product.stock}`);
            return;
        }
        
        cart[index].qty = newQty;
        cart[index].subtotal = cart[index].price * newQty;
        renderCart();
    }
    
    function removeFromCart(index) {
        cart.splice(index, 1);
        renderCart();
    }
    
    function formatNumber(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
    
    // Update prices when customer type changes
    document.querySelector('select[name="customer_type"]').addEventListener('change', function() {
        // Update all prices in cart
        cart.forEach(item => {
            const product = allProducts.find(p => p.id == item.id);
            if (product) {
                item.price = getPriceForCustomerType(product);
                item.subtotal = item.price * item.qty;
            }
        });
        renderCart();
    });
    
    // Prevent form submission if cart is empty
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        if (cart.length === 0) {
            e.preventDefault();
            alert("Keranjang belanja masih kosong!");
        }
        
        // Validate DP amount if DP is checked
        const isDP = document.getElementById('is_dp').checked;
        if (isDP) {
            const dpAmount = parseFloat(document.getElementById('down_payment').value) || 0;
            const totalAmount = calculateTotalAmount();
            
            if (dpAmount <= 0) {
                e.preventDefault();
                alert("Jumlah DP harus lebih dari 0!");
                return;
            }
            
            if (dpAmount >= totalAmount) {
                e.preventDefault();
                alert("Jumlah DP tidak boleh sama atau melebihi total harga!");
                return;
            }
        }
    });
    
    // Toggle DP field visibility
    function toggleDPField() {
        const isDP = document.getElementById('is_dp').checked;
        const dpAmountGroup = document.getElementById('dp_amount_group');
        const dpInput = document.getElementById('down_payment');
        const dpInfo = document.getElementById('dp_info');
        
        if (isDP) {
            dpAmountGroup.style.display = 'block';
            const totalAmount = calculateTotalAmount();
            dpInput.max = totalAmount - 1;
            dpInput.value = Math.floor(totalAmount * 0.5); // Default 50% of total
            updateDPInfo();
        } else {
            dpAmountGroup.style.display = 'none';
            dpInput.value = '';
        }
    }
    
    // Calculate total amount from cart
    function calculateTotalAmount() {
        let total = 0;
        cart.forEach(item => {
            total += item.subtotal;
        });
        return total;
    }
    
    // Update DP info text
    function updateDPInfo() {
        const dpAmount = parseFloat(document.getElementById('down_payment').value) || 0;
        const totalAmount = calculateTotalAmount();
        const remaining = totalAmount - dpAmount;
        const dpInfo = document.getElementById('dp_info');
        
        dpInfo.innerHTML = `
            Total: Rp ${formatNumber(totalAmount)}<br>
            DP: Rp ${formatNumber(dpAmount)}<br>
            Sisa: Rp ${formatNumber(remaining)}
        `;
    }
    
    // Update DP info when DP amount changes
    document.getElementById('down_payment')?.addEventListener('input', function() {
        updateDPInfo();
    });
    
    // Update DP info when cart changes
    function updateDPInfoOnCartChange() {
        if (document.getElementById('is_dp').checked) {
            updateDPInfo();
            const totalAmount = calculateTotalAmount();
            const dpInput = document.getElementById('down_payment');
            const currentDP = parseFloat(dpInput.value) || 0;
            
            if (currentDP > totalAmount) {
                dpInput.value = Math.floor(totalAmount * 0.5);
            }
            dpInput.max = totalAmount - 1;
        }
    }
    
    // Override renderCart to update DP info
    const originalRenderCart = renderCart;
    renderCart = function() {
        originalRenderCart();
        updateDPInfoOnCartChange();
    };
    
    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        renderCart();
        loadPopularProducts();
    });
</script>
@endsection