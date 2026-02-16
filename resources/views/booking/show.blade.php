@extends('layouts.app')

@section('title', 'Detail Booking Studio')
@section('page-title', 'Detail Booking Studio')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2 class="mb-0">Detail Booking</h2>
            <p class="text-muted">Informasi lengkap booking studio</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('booking.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Booking</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Kode Booking</th>
                                    <td>{{ $booking->booking_code }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal Dibuat</th>
                                    <td>{{ $booking->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Dibuat Oleh</th>
                                    <td>{{ $booking->user->name }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'confirmed' => 'info',
                                                'completed' => 'success',
                                                'cancelled' => 'danger'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$booking->status] ?? 'secondary' }}">
                                            {{ $booking->status_label }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Nama Pelanggan</th>
                                    <td>{{ $booking->customer_name }}</td>
                                </tr>
                                <tr>
                                    <th>Nomor WhatsApp</th>
                                    <td>{{ $booking->customer_phone }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal Booking</th>
                                    <td>{{ $booking->booking_date->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Jumlah Anggota</th>
                                    <td>{{ $booking->number_of_people }} orang</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Kategori Studio</th>
                                    <td>{{ $booking->studio_category_label }}</td>
                                </tr>
                                <tr>
                                    <th>Tipe Paket</th>
                                    <td>{{ $booking->package_type }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Harga Paket</th>
                                    <td>Rp {{ number_format($booking->package_price, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th>Total Amount</th>
                                    <td>Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($booking->notes)
                    <div class="mt-3">
                        <h6>Catatan:</h6>
                        <div class="alert alert-light">
                            {{ $booking->notes }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Informasi Pembayaran</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="60%">Harga Paket Dasar</th>
                            <td class="text-end">Rp {{ number_format($booking->base_package_price, 0, ',', '.') }}</td>
                        </tr>
                        
                        @if($booking->additional_charge > 0)
                        <tr>
                            <th>Tambahan Orang ({{ $booking->number_of_people > 15 ? $booking->number_of_people - 15 : 0 }} orang)</th>
                            <td class="text-end text-danger">+ Rp {{ number_format($booking->additional_charge, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        
                        <tr class="table-secondary">
                            <th><strong>Subtotal Paket</strong></th>
                            <td class="text-end"><strong>Rp {{ number_format($booking->package_price, 0, ',', '.') }}</strong></td>
                        </tr>
                        
                        <tr>
                            <th>Down Payment (DP)</th>
                            <td class="text-end">Rp {{ number_format($booking->down_payment, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Sisa Pembayaran</th>
                            <td class="text-end">Rp {{ number_format($booking->remaining_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="table-active">
                            <th><strong>TOTAL AMOUNT (HARGA FULL)</strong></th>
                            <td class="text-end"><strong>Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</strong></td>
                        </tr>
                    </table>

                    @if($booking->transaction)
                    <div class="alert alert-info mt-3">
                        <h6 class="alert-heading">Sudah Menjadi Transaksi</h6>
                        <p class="mb-0">
                            Invoice: {{ $booking->transaction->invoice_id }}<br>
                            Tanggal: {{ $booking->transaction->created_at->format('d/m/Y H:i') }}<br>
                            <strong>Total Dibayar: Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</strong>
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Aksi</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('booking.edit', $booking) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Booking
                        </a>
                        
                        @if($booking->status !== 'completed')
                            <form action="{{ route('booking.markAsDone', $booking) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100" onclick="return confirm('Apakah booking ini sudah selesai?')">
                                    <i class="fas fa-check"></i> Mark as Done
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('booking.invoice', $booking) }}" class="btn btn-primary" target="_blank">
                            <i class="fas fa-receipt"></i> Lihat Invoice
                        </a>

                        <form action="{{ route('booking.destroy', $booking) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Apakah Anda yakin ingin menghapus booking ini?')">
                                <i class="fas fa-trash"></i> Hapus Booking
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection