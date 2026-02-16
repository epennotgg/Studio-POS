@extends('layouts.app')

@section('title', 'Edit Booking Studio')
@section('page-title', 'Edit Booking Studio')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2 class="mb-0">Edit Booking</h2>
            <p class="text-muted">Perbarui informasi booking studio</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('booking.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar Booking
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('booking.update', $booking) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="customer_name" class="form-label">Nama Pelanggan *</label>
                            <input type="text" class="form-control" id="customer_name" name="customer_name" 
                                   value="{{ old('customer_name', $booking->customer_name) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="customer_phone" class="form-label">Nomor WhatsApp *</label>
                            <input type="text" class="form-control" id="customer_phone" name="customer_phone" 
                                   value="{{ old('customer_phone', $booking->customer_phone) }}" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="studio_category" class="form-label">Kategori Studio *</label>
                            <select class="form-control" id="studio_category" name="studio_category" required>
                                <option value="">Pilih Kategori</option>
                                <option value="family_graduation" {{ old('studio_category', $booking->studio_category) == 'family_graduation' ? 'selected' : '' }}>Family/Graduation</option>
                                <option value="prewedding_indoor" {{ old('studio_category', $booking->studio_category) == 'prewedding_indoor' ? 'selected' : '' }}>Prewedding Indoor</option>
                                <option value="studio_outdoor" {{ old('studio_category', $booking->studio_category) == 'studio_outdoor' ? 'selected' : '' }}>Studio Outdoor</option>
                                <option value="sewa_event" {{ old('studio_category', $booking->studio_category) == 'sewa_event' ? 'selected' : '' }}>Sewa Event</option>
                                <option value="custom" {{ old('studio_category', $booking->studio_category) == 'custom' ? 'selected' : '' }}>Custom</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="package_type" class="form-label">Tipe Paket *</label>
                            <select class="form-control" id="package_type" name="package_type" required>
                                <option value="{{ $booking->package_type }}">{{ $booking->package_type }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="booking_date" class="form-label">Tanggal & Waktu Booking *</label>
                            <input type="datetime-local" class="form-control" id="booking_date" name="booking_date" 
                                   value="{{ old('booking_date', $booking->booking_date->format('Y-m-d\TH:i')) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="number_of_people" class="form-label">Jumlah Anggota *</label>
                            <input type="number" class="form-control" id="number_of_people" name="number_of_people" 
                                   value="{{ old('number_of_people', $booking->number_of_people) }}" min="1" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="down_payment" class="form-label">Total DP/Bayar *</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="down_payment" name="down_payment" 
                                       value="{{ old('down_payment', $booking->down_payment) }}" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="custom_price" class="form-label">Harga Custom (jika pilih Custom)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="custom_price" name="custom_price" 
                                       value="{{ old('custom_price', $booking->package_price) }}" min="0" 
                                       {{ $booking->studio_category == 'custom' ? '' : 'disabled' }}>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Metode Pembayaran *</label>
                            <select class="form-control" id="payment_method" name="payment_method" required>
                                <option value="Cash" {{ old('payment_method', $booking->payment_method) == 'Cash' ? 'selected' : '' }}>Tunai (Cash)</option>
                                <option value="Transfer" {{ old('payment_method', $booking->payment_method) == 'Transfer' ? 'selected' : '' }}>Transfer Bank</option>
                                <option value="QRIS" {{ old('payment_method', $booking->payment_method) == 'QRIS' ? 'selected' : '' }}>QRIS</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Catatan</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $booking->notes) }}</textarea>
                </div>

                <div class="alert alert-info">
                    <h6 class="alert-heading">Informasi Booking Saat Ini:</h6>
                    <p class="mb-1"><strong>Kode Booking:</strong> {{ $booking->booking_code }}</p>
                    <p class="mb-1"><strong>Status:</strong> {{ $booking->status_label }}</p>
                    <p class="mb-1"><strong>Total Amount:</strong> Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</p>
                    <p class="mb-0"><strong>Remaining Amount:</strong> Rp {{ number_format($booking->remaining_amount, 0, ',', '.') }}</p>
                </div>

                <div class="alert alert-warning">
                    <h6 class="alert-heading">Perhatian:</h6>
                    <p class="mb-0">Mengedit booking akan menghitung ulang harga berdasarkan paket dan jumlah orang yang baru.</p>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('booking.show', $booking) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Simple JavaScript untuk handle perubahan kategori studio
    document.getElementById('studio_category').addEventListener('change', function() {
        var category = this.value;
        var packageSelect = document.getElementById('package_type');
        var customPriceInput = document.getElementById('custom_price');
        
        // Reset package select
        packageSelect.innerHTML = '<option value="">Pilih Paket</option>';
        
        // Enable/disable custom price input
        if (category === 'custom') {
            customPriceInput.disabled = false;
            customPriceInput.required = true;
        } else {
            customPriceInput.disabled = true;
            customPriceInput.required = false;
            customPriceInput.value = '';
        }
    });
</script>
@endpush
@endsection