@extends('layouts.app')

@section('title', 'Booking Studio')
@section('page-title', 'Booking Studio')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2 class="mb-0">Daftar Booking Studio</h2>
            <p class="text-muted">Kelola semua booking studio yang telah dibuat</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('booking.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Buat Booking Baru
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Kode Booking</th>
                            <th>Pelanggan</th>
                            <th>Kategori</th>
                            <th>Paket</th>
                            <th>Tanggal Booking</th>
                            <th>Total</th>
                            <th>DP</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                            <tr>
                                <td>
                                    <strong>{{ $booking->booking_code }}</strong><br>
                                    <small class="text-muted">Oleh: {{ $booking->user->name }}</small>
                                </td>
                                <td>
                                    {{ $booking->customer_name }}<br>
                                    <small class="text-muted">{{ $booking->customer_phone }}</small>
                                </td>
                                <td>{{ $booking->studio_category_label }}</td>
                                <td>{{ $booking->package_type }}</td>
                                <td>
                                    {{ $booking->booking_date->format('d/m/Y H:i') }}<br>
                                    <small class="text-muted">{{ $booking->number_of_people }} orang</small>
                                </td>
                                <td>Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($booking->down_payment, 0, ',', '.') }}</td>
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
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('booking.show', $booking) }}" class="btn btn-info" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('booking.invoice', $booking) }}" class="btn btn-primary" title="Invoice (Screenshot only)" target="_blank">
                                            <i class="fas fa-receipt"></i>
                                        </a>
                                        <a href="{{ route('booking.edit', $booking) }}" class="btn btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($booking->status !== 'completed')
                                            <form action="{{ route('booking.markAsDone', $booking) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success" title="Mark as Done" onclick="return confirm('Apakah booking ini sudah selesai?')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('booking.destroy', $booking) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus booking ini?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-calendar-times fa-2x mb-3"></i><br>
                                    Belum ada booking yang dibuat
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection