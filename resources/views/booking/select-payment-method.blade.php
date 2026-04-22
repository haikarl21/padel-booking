@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <h3 class="card-title mb-2">
                        <i class="fas fa-credit-card"></i> Pilih Metode Pembayaran
                    </h3>
                    <p class="text-muted mb-0">Silakan pilih metode pembayaran untuk booking Anda</p>
                </div>
            </div>

            <!-- Booking Summary -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Detail Booking</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Lapangan:</strong> 
                                <span>{{ $booking->court->name ?? 'N/A' }}</span>
                            </p>
                            <p class="mb-2">
                                <strong>Tanggal:</strong> 
                                <span>{{ $booking->date->format('d M Y') }}</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Jam:</strong> 
                                <span>{{ $booking->timeSlot->start_time }} - {{ $booking->timeSlot->end_time }}</span>
                            </p>
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-0">
                                <strong>Total Pembayaran:</strong>
                            </p>
                        </div>
                        <div class="col-md-6 text-end">
                            <p class="mb-0 h5 text-success">
                                <strong>Rp {{ number_format($booking->total_price, 0, ',', '.') }}</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">Pilih Metode Pembayaran</h5>

                    <form method="POST" action="{{ route('payment.store-method', $booking) }}" id="methodForm">
                        @csrf

                        <div class="row">
                            <!-- Bank Transfer Option -->
                            <div class="col-md-6 mb-3">
                                <label class="payment-method-card cursor-pointer">
                                    <input type="radio" name="payment_method" value="bank_transfer" class="form-check-input" checked>
                                    <div class="card h-100 border-info">
                                        <div class="card-body text-center">
                                            <i class="fas fa-bank fa-3x text-primary mb-3"></i>
                                            <h6 class="card-title mb-2">Bank Transfer Manual</h6>
                                            <p class="card-text text-muted small mb-2">
                                                Transfer langsung ke rekening kami
                                            </p>
                                            <div class="badge bg-success">Direkomendasikan</div>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <!-- E-Wallet Option (Coming Soon) -->
                            {{-- <div class="col-md-6 mb-3">
                                <label class="payment-method-card cursor-pointer opacity-50">
                                    <input type="radio" name="payment_method" value="ewallet" class="form-check-input" disabled>
                                    <div class="card h-100 border-secondary">
                                        <div class="card-body text-center">
                                            <i class="fas fa-wallet fa-3x text-secondary mb-3"></i>
                                            <h6 class="card-title mb-2">E-Wallet</h6>
                                            <p class="card-text text-muted small mb-2">
                                                Segera Hadir
                                            </p>
                                            <div class="badge bg-secondary">Coming Soon</div>
                                        </div>
                                    </div>
                                </label>
                            </div> --}}

                            <!-- QR Code Option-->
                            <div class="col-md-6 mb-3">
                                <label class="payment-method-card cursor-pointer">
                                    <input type="radio" name="payment_method" value="qrcode_dynamic" class="form-check-input">
                                    <div class="card h-100 border-warning">
                                        <div class="card-body text-center">
                                            <i class="fas fa-qrcode fa-3x text-warning mb-3"></i>
                                            <h6 class="card-title mb-2">QRIS/QR Code</h6>
                                            <p class="card-text text-muted small mb-2">
                                                Scan QRIS dengan aplikasi bank
                                            </p>
                                            <div class="badge bg-success">Tersedia</div>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <!-- Cicilan Option (Coming Soon) -->
                            {{-- <div class="col-md-6 mb-3">
                                <label class="payment-method-card cursor-pointer opacity-50">
                                    <input type="radio" name="payment_method" value="installment" class="form-check-input" disabled>
                                    <div class="card h-100 border-secondary">
                                        <div class="card-body text-center">
                                            <i class="fas fa-calendar-alt fa-3x text-secondary mb-3"></i>
                                            <h6 class="card-title mb-2">Cicilan</h6>
                                            <p class="card-text text-muted small mb-2">
                                                Segera Hadir
                                            </p>
                                            <div class="badge bg-secondary">Coming Soon</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            --}}
                        </div>

                        <!-- Info Box -->
                        <div class="alert alert-info mb-4 mt-4">
                            <i class="fas fa-info-circle"></i>
                            <strong>Bank Transfer</strong> adalah metode pembayaran tercepat dan termudah. 
                            Anda akan mendapatkan nomor rekening dan total pembayaran unik setelah memilih metode ini.
                        </div>

                        <!-- Buttons -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-arrow-right"></i> Lanjutkan Pembayaran
                            </button>
                            <a href="{{ route('booking.detail', $booking) }}" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="alert alert-warning mt-4 mb-0">
                <h6 class="alert-heading">
                    <i class="fas fa-exclamation-triangle"></i> Penting!
                </h6>
                <ul class="mb-0">
                    <li>Pembayaran harus dilakukan dalam <strong>30 menit</strong> setelah memilih metode</li>
                    <li>Jangan habiskan sesi booking Anda - lanjutkan pembayaran sampai selesai</li>
                    <li>Jika ada pertanyaan, hubungi admin melalui chat atau nomor telepon yang tertera</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
    .payment-method-card {
        display: block;
        cursor: pointer;
    }

    .payment-method-card input[type="radio"] {
        display: none;
    }

    .payment-method-card input[type="radio"]:checked + .card {
        border: 2px solid #0d6efd !important;
        box-shadow: 0 0.5rem 1rem rgba(13, 110, 253, 0.3);
    }

    .payment-method-card .card {
        transition: all 0.3s ease;
    }

    .payment-method-card:hover .card:not(.opacity-50) {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
</style>
@endsection
