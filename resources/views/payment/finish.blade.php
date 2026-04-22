@extends('layouts.app')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <!-- Payment Pending Card -->
            <div class="card border-0 shadow-lg">
                <div class="card-body text-center py-5">
                    <i class="fas fa-hourglass-half text-warning" style="font-size: 4rem; margin-bottom: 1rem;"></i>
                    <h3 class="text-warning mb-3">Pembayaran Sedang Diproses</h3>
                    <p class="text-muted mb-4">
                        Terimakasih telah melakukan transaksi. Silakan tunggu sebentar...
                    </p>
                    
                    <!-- Booking Info -->
                    <div class="alert alert-light border mb-4">
                        <p class="mb-2">
                            <strong>Kode Booking:</strong> <span class="badge bg-primary">{{ $booking->booking_code }}</span>
                        </p>
                        <p class="mb-2">
                            <strong>Order ID:</strong> <code>{{ $order_id }}</code>
                        </p>
                        <p class="mb-0">
                            <strong>Total Pembayaran:</strong> <span class="h6 text-primary">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
                        </p>
                    </div>

                    <!-- Status Info -->
                    <div class="alert alert-info mb-4">
                        <p class="mb-2">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Status Pembayaran Saat Ini:</strong>
                        </p>
                        <h5 class="mb-0">
                            <span class="badge bg-warning p-2">
                                <i class="fas fa-spinner fa-spin"></i> {{ ucfirst($payment->transaction_status ?? 'pending') }}
                            </span>
                        </h5>
                    </div>

                    <!-- Auto Check Status -->
                    <div class="mb-4">
                        <p class="text-muted small">Sistem akan otomatis mengecek status pembayaran...</p>
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                        <a href="{{ route('booking.receipt', $booking) }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-check-circle"></i> Lihat Detail Booking
                        </a>
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-arrow-left"></i> Kembali ke Beranda
                        </a>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="alert alert-info mt-4" role="alert">
                <h6 class="alert-heading">
                    <i class="fas fa-lightbulb"></i> Informasi
                </h6>
                <ul class="mb-0">
                    <li>Jika pembayaran berhasil, status booking akan berubah menjadi "Approved"</li>
                    <li>Anda akan menerima email konfirmasi pembayaran</li>
                    <li>Jika pembayaran gagal, silakan coba lagi dengan metode pembayaran berbeda</li>
                    <li>Hubungi admin jika ada masalah dengan pembayaran Anda</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto check payment status setiap 2 detik
    let checkCounter = 0;
    const maxChecks = 30; // Check selama 60 detik (30 x 2 detik)

    function checkPaymentStatus() {
        checkCounter++;

        if (checkCounter > maxChecks) {
            // Redirect ke detail booking
            window.location.href = "{{ route('booking.receipt', $booking) }}";
            return;
        }

        fetch("{{ route('payment.check-status', $payment) }}", {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Payment status:', data);

            if (data.success) {
                const status = data.transaction_status;

                // Update UI with current status
                document.querySelector('.badge').textContent = status.toUpperCase();

                // Jika settlement/capture, langsung redirect ke success
                if (status === 'settlement' || status === 'capture') {
                    document.querySelector('.alert-info').innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> 
                            <strong>Pembayaran Berhasil!</strong>
                        </div>
                    `;
                    setTimeout(() => {
                        window.location.href = "{{ route('booking.receipt', $booking) }}";
                    }, 2000);
                    return;
                }

                // Jika pending, continue checking
                if (status === 'pending') {
                    setTimeout(checkPaymentStatus, 2000);
                    return;
                }

                // Jika failed status, redirect
                if (status === 'cancelled' || status === 'deny' || status === 'expire') {
                    document.querySelector('.alert-info').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle"></i> 
                            <strong>Pembayaran Gagal!</strong>
                        </div>
                    `;
                    setTimeout(() => {
                        window.location.href = "{{ route('booking.detail', $booking) }}";
                    }, 3000);
                    return;
                }
            }

            // Continue checking
            setTimeout(checkPaymentStatus, 2000);
        })
        .catch(error => {
            console.error('Error checking status:', error);
            // Continue checking anyway
            setTimeout(checkPaymentStatus, 2000);
        });
    }

    // Start checking status on page load
    document.addEventListener('DOMContentLoaded', function() {
        checkPaymentStatus();
    });
</script>
@endsection
