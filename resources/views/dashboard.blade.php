@extends('layouts.app')

@section('content')
<div class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <h2 class="display-5 fw-bold">Dasbor</h2>
                    <a href="{{ route('courts') }}" class="btn btn-primary-custom">
                        <i class="fas fa-plus me-2"></i>Booking Baru
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card-custom h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-check fa-2x mb-3" style="color: #FFA500;"></i>
                        <h3 class="h2 mb-1" style="color: #FFA500;">{{ $bookings->total() }}</h3>
                        <p style="color: #ffffff;">Total Booking</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-custom h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle fa-2x mb-3" style="color: #90EE90;"></i>
                        <h3 class="h2 mb-1" style="color: #90EE90;">{{ $bookings->where('status', 'confirmed')->count() }}</h3>
                        <p style="color: #ffffff;">Dikonfirmasi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-custom h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-hourglass-half fa-2x mb-3" style="color: #FFD700;"></i>
                        <h3 class="h2 mb-1" style="color: #FFD700;">{{ $bookings->where('status', 'pending')->count() }}</h3>
                        <p style="color: #ffffff;">Menunggu</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-custom h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-times-circle fa-2x mb-3" style="color: #FF6B6B;"></i>
                        <h3 class="h2 mb-1" style="color: #FF6B6B;">{{ $bookings->where('status', 'cancelled')->count() }}</h3>
                        <p style="color: #ffffff;">Dibatalkan</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bookings Table -->
        <div class="row">
            <div class="col-12">
                <div class="card-custom">
                    <div class="card-body">
                        <h4 class="fw-bold mb-4">
                            <i class="fas fa-list me-2" style="color: #FFA500;"></i>Booking Terbaru
                        </h4>

                        @if($bookings->count() > 0)
                            <div class="table-responsive">
                                <table class="table" style="border-color: #333333;">
                                    <thead style="border-color: #333333;">
                                        <tr style="border-color: #333333;">
                                            <th style="color: #ffffff;">Referensi</th>
                                            <th style="color: #ffffff;">Lapangan</th>
                                            <th style="color: #ffffff;">Tanggal</th>
                                            <th style="color: #ffffff;">Pelanggan</th>
                                            <th style="color: #ffffff;">Harga</th>
                                            <th style="color: #ffffff;">Status</th>
                                            <th style="color: #ffffff;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody style="border-color: #333333;">
                                        @foreach($bookings as $booking)
                                            <tr style="border-color: #333333;">
                                                <td>
                                                    <span class="fw-bold" style="color: #FFA500;">{{ $booking->booking_code }}</span>
                                                </td>
                                                <td>
                                                    <span style="color: #ffffff;">{{ $booking->court->name }}</span>
                                                </td>
                                                <td>
                                                    <span style="color: #ffffff;">{{ $booking->date->format('d M Y') }}</span>
                                                </td>
                                                <td>
                                                    <span style="color: #ffffff;">{{ $booking->customer_name }}</span>
                                                </td>
                                                <td>
                                                    <span class="text-warning fw-bold">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $booking->status === 'confirmed' ? 'bg-success' : ($booking->status === 'pending' ? 'bg-warning' : 'bg-danger') }} fs-6">
                                                        {{ ucfirst($booking->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('booking.detail', $booking) }}" class="btn btn-primary-custom btn-sm">
                                                        <i class="fas fa-eye me-1"></i>View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center mt-4">
                                {{ $bookings->links('pagination::bootstrap-4') }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-4x mb-3" style="color: #666666;"></i>
                                <h5 style="color: #ffffff;">Belum Ada Booking</h5>
                                <p style="color: #666666;">Mulai dengan membuat booking baru untuk lapangan padel kami</p>
                                <a href="{{ route('courts') }}" class="btn btn-primary-custom mt-3">
                                    <i class="fas fa-plus me-2"></i>Buat Booking
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table {
        color: #ffffff;
    }

    .table th, .table td {
        padding: 1rem 0.75rem;
        border-top-color: #333333 !important;
        border-bottom-color: #333333 !important;
    }

    .table tbody tr {
        border-top-color: #333333 !important;
        border-bottom-color: #333333 !important;
    }
</style>
@endsection
