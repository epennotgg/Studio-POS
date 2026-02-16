@extends('layouts.app')

@section('title', 'Buat Booking Studio')
@section('page-title', 'Buat Booking Studio')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2 class="mb-0">Buat Booking Baru</h2>
            <p class="text-muted">Isi form untuk membuat booking studio</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('booking.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar Booking
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('booking.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="customer_name" class="form-label">Nama Pelanggan *</label>
                            <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="customer_phone" class="form-label">Nomor WhatsApp *</label>
                            <input type="text" class="form-control" id="customer_phone" name="customer_phone" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="studio_category" class="form-label">Kategori Studio *</label>
                            <select class="form-control" id="studio_category" name="studio_category" required>
                                <option value="">Pilih Kategori</option>
                                <option value="family_graduation">Family/Graduation</option>
                                <option value="prewedding_indoor">Prewedding Indoor</option>
                                <option value="studio_outdoor">Studio Outdoor</option>
                                <option value="sewa_event">Sewa Event</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="package_type" class="form-label">Tipe Paket *</label>
                            <select class="form-control" id="package_type" name="package_type" required>
                                <option value="">Pilih Kategori Studio terlebih dahulu</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="booking_date" class="form-label">Tanggal & Waktu Booking *</label>
                            <input type="datetime-local" class="form-control" id="booking_date" name="booking_date" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="number_of_people" class="form-label">Jumlah Anggota *</label>
                            <input type="number" class="form-control" id="number_of_people" name="number_of_people" min="1" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="down_payment" class="form-label">Total DP/Bayar *</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="down_payment" name="down_payment" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="custom_price" class="form-label">Harga Custom (jika pilih Custom)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="custom_price" name="custom_price" min="0" disabled>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Metode Pembayaran *</label>
                            <select class="form-control" id="payment_method" name="payment_method" required>
                                <option value="Cash">Tunai (Cash)</option>
                                <option value="Transfer">Transfer Bank</option>
                                <option value="QRIS">QRIS</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Catatan</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                </div>

                <div class="alert alert-info">
                    <h6 class="alert-heading">Informasi Harga:</h6>
                    <div id="price_info">
                        Pilih kategori dan paket untuk melihat detail harga
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Booking
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Deklarasi packages menggunakan window object untuk menghindari error
    window.packages = @json($packages);
    
    document.getElementById('studio_category').addEventListener('change', function() {
        const category = this.value;
        const packageSelect = document.getElementById('package_type');
        const customPriceInput = document.getElementById('custom_price');
        
        packageSelect.innerHTML = '<option value="">Pilih Paket</option>';
        
        if (category && window.packages[category]) {
            window.packages[category].forEach(package => {
                const option = document.createElement('option');
                option.value = package;
                option.textContent = package;
                packageSelect.appendChild(option);
            });
        }
        
        // Enable/disable custom price input
        if (category === 'custom') {
            customPriceInput.disabled = false;
            customPriceInput.required = true;
        } else {
            customPriceInput.disabled = true;
            customPriceInput.required = false;
            customPriceInput.value = '';
        }
        
        updatePriceInfo();
    });
    
    document.getElementById('package_type').addEventListener('change', updatePriceInfo);
    document.getElementById('number_of_people').addEventListener('input', updatePriceInfo);
    
    function updatePriceInfo() {
        const category = document.getElementById('studio_category').value;
        const packageType = document.getElementById('package_type').value;
        const numberOfPeople = parseInt(document.getElementById('number_of_people').value) || 0;
        const priceInfo = document.getElementById('price_info');
        
        if (!category || !packageType) {
            priceInfo.innerHTML = 'Pilih kategori dan paket untuk melihat detail harga';
            return;
        }
        
        let price = 0;
        let additionalInfo = '';
        
        // Logika harga sesuai dengan controller
        switch(category) {
            case 'family_graduation':
                if (packageType.includes('Paket 1 250k')) price = 250000;
                else if (packageType.includes('Paket 2 450k')) price = 450000;
                else if (packageType.includes('Paket 3 750k')) {
                    price = 750000;
                    if (numberOfPeople > 15) {
                        const additionalPeople = numberOfPeople - 15;
                        const additionalCharge = additionalPeople * 50000;
                        price += additionalCharge;
                        additionalInfo = '<br>Additional charge: Rp ' + additionalCharge.toLocaleString() + ' (' + additionalPeople + ' orang × Rp 50.000)';
                    }
                }
                break;
            case 'prewedding_indoor':
                if (packageType.includes('Paket 1 350k')) price = 350000;
                else if (packageType.includes('Paket 2 500k')) price = 500000;
                else if (packageType.includes('Paket 3 850k')) price = 850000;
                break;
            case 'studio_outdoor':
                if (packageType.includes('Paket 1 250k')) price = 250000;
                else if (packageType.includes('Paket 2 750k')) price = 750000;
                break;
            case 'sewa_event':
                if (packageType.includes('Bronze')) price = 1750000;
                else if (packageType.includes('Silver')) price = 2750000;
                else if (packageType.includes('Gold')) price = 4250000;
                break;
            case 'custom':
                price = parseInt(document.getElementById('custom_price').value) || 0;
                break;
        }
        
        const totalAmount = price;
        priceInfo.innerHTML = '<strong>Harga Paket:</strong> Rp ' + price.toLocaleString() + '<br>' +
                             '<strong>Total Amount:</strong> Rp ' + totalAmount.toLocaleString() + additionalInfo;
    }
</script>
@endpush
@endsection